<?php

namespace SpiffyRoutes;

use ArrayObject;
use ReflectionClass;
use SpiffyRoutes\Listener\ActionAnnotationsListener;
use SpiffyRoutes\Listener\ControllerAnnotationsListener;
use Zend\Cache\Storage\Adapter\Memory;
use Zend\Cache\Storage\StorageInterface;
use Zend\Code\Annotation\AnnotationCollection;
use Zend\Code\Annotation\AnnotationManager;
use Zend\Code\Annotation\Parser;
use Zend\Code\Reflection\ClassReflection;
use Zend\Code\Reflection\MethodReflection;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\Controller\ControllerManager;

class RouteBuilder
{
    /**
     * @var AnnotationManager
     */
    protected $annotationManager;

    /**
     * @var StorageInterface
     */
    protected $cacheAdapter;

    /**
     * @var array
     */
    protected $controllers = array();

    /**
     * @var array Default annotations to register
     */
    protected $defaultAnnotations = array(
        'SpiffyRoutes\\Annotation\\Literal',
        'SpiffyRoutes\\Annotation\\Root',
    );

    /**
     * @var ControllerManager
     */
    protected $controllerManager;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @param ControllerManager $controllerManager
     */
    public function __construct(ControllerManager $controllerManager)
    {
        $this->controllerManager = $controllerManager;
    }

    /**
     * Set annotation manager to use when building form from annotations
     *
     * @param  AnnotationManager $annotationManager
     * @return RouteBuilder
     */
    public function setAnnotationManager(AnnotationManager $annotationManager)
    {
        $parser = new Parser\DoctrineAnnotationParser();
        foreach ($this->defaultAnnotations as $class) {
            $parser->registerAnnotation($class);
        }
        $annotationManager->attach($parser);
        $this->annotationManager = $annotationManager;
        return $this;
    }

    /**
     * Retrieve annotation manager
     *
     * If none is currently set, creates one with default annotations.
     *
     * @return AnnotationManager
     */
    public function getAnnotationManager()
    {
        if ($this->annotationManager) {
            return $this->annotationManager;
        }

        $this->setAnnotationManager(new AnnotationManager());
        return $this->annotationManager;
    }

    /**
     * Set event manager instance
     *
     * @param  EventManagerInterface $eventManager
     * @return RouteBuilder
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $eventManager->setIdentifiers(array(
            __CLASS__,
            get_class($this),
        ));
        $eventManager->attach(new ControllerAnnotationsListener());
        $eventManager->attach(new ActionAnnotationsListener());
        $this->eventManager = $eventManager;
        return $this;
    }


    /**
     * Get event manager
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (null === $this->eventManager) {
            $this->setEventManager(new EventManager());
        }
        return $this->eventManager;
    }

    /**
     * @param \Zend\Cache\Storage\StorageInterface $cacheAdapter
     * @return RouteBuilder
     */
    public function setCacheAdapter($cacheAdapter)
    {
        $this->cacheAdapter = $cacheAdapter;
        return $this;
    }

    /**
     * @return \Zend\Cache\Storage\StorageInterface
     */
    public function getCacheAdapter()
    {
        if (!$this->cacheAdapter) {
            $this->cacheAdapter = new Memory();
        }
        return $this->cacheAdapter;
    }

    /**
     * Builds the router config from controller/action annotations.
     */
    public function getRouterConfig()
    {
        $annotationManager = $this->getAnnotationManager();
        $controllers       = $this->generateControllerList();
        $routerConfig      = array();

        foreach ($controllers as $controllerName => $controller) {
            $reflection  = new ClassReflection($controller);
            $annotations = $reflection->getAnnotations($annotationManager);

            $controllerSpec         = new ArrayObject();
            $controllerSpec['name'] = $controllerName;

            if ($annotations instanceof AnnotationCollection) {
                $this->configureController($annotations, $controllerSpec);
            }

            foreach ($reflection->getMethods() as $method) {
                $name = $this->getActionName($method->getName());
                if (!$name) {
                    continue;
                }

                $actionSpec         = new ArrayObject();
                $actionSpec['name'] = $name;

                $annotations = $method->getAnnotations($annotationManager);

                if ($annotations instanceof AnnotationCollection) {
                    $this->configureAction($annotations, $controllerSpec, $actionSpec);
                }

                if (isset($actionSpec['type'])) {
                    $name = $this->discoverName($annotations, $controllerSpec, $actionSpec);
                    if ($name) {
                        $routerConfig[$name] = $actionSpec->getArrayCopy();
                    }
                }
            }
        }
        return $routerConfig;
    }

    /**
     * @param string $input
     * @return string|null
     */
    protected function getActionName($input)
    {
        if (preg_match('/^(.*)Action$/', $input, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * @param AnnotationCollection $annotations
     * @param ArrayObject $controllerSpec
     * @param ArrayObject $actionSpec
     * @return mixed|null
     */
    protected function discoverName(
        AnnotationCollection $annotations,
        ArrayObject $controllerSpec,
        ArrayObject $actionSpec
    ) {
        $results = $this->getEventManager()->trigger(__FUNCTION__, $this, array(
            'annotations'    => $annotations,
            'controllerSpec' => $controllerSpec,
            'actionSpec'     => $actionSpec
        ), function ($r) {
            return (true === $r);
        });
        return is_string($results->last()) ? $results->last() : null;
    }

    /**
     * @param AnnotationCollection $annotations
     * @param ArrayObject $controllerSpec
     */
    protected function configureController(AnnotationCollection $annotations, ArrayObject $controllerSpec)
    {
        $eventManager = $this->getEventManager();
        foreach ($annotations as $annotation) {
            $eventManager->trigger(__FUNCTION__, $this, array(
                'annotation'     => $annotation,
                'controllerSpec' => $controllerSpec
            ));
        }
    }

    /**
     * @param AnnotationCollection $annotations
     * @param ArrayObject $controllerSpec
     * @param ArrayObject $actionSpec
     */
    protected function configureAction(
        AnnotationCollection $annotations,
        ArrayObject $controllerSpec,
        ArrayObject $actionSpec
    ) {
        if ($this->checkForExclude($annotations)) {
            return;
        }

        $eventManager = $this->getEventManager();
        foreach ($annotations as $annotation) {
            $eventManager->trigger(__FUNCTION__, $this, array(
                'annotation'     => $annotation,
                'controllerSpec' => $controllerSpec,
                'actionSpec'     => $actionSpec
            ));
        }
    }

    /**
     * @param AnnotationCollection $annotations
     * @return bool
     */
    protected function checkForExclude(AnnotationCollection $annotations)
    {
        $results = $this->getEventManager()->trigger(__FUNCTION__, $this, array(
            'annotations' => $annotations,
        ), function ($r) {
            return (true === $r);
        });
        return (bool) $results->last();
    }

    /**
     * Generates a list of controllers to load. InvokableClasses are registered as strings
     * but factories have to be created through the controller manager in order to be
     * reflected.
     *
     * @return array
     */
    protected function generateControllerList()
    {
        if ($this->controllers) {
            return $this->controllers;
        }

        $manager        = $this->controllerManager;
        $canonicalNames = $manager->getCanonicalNames();
        $controllers    = array();

        foreach ($canonicalNames as $name => $canonical) {
            try {
                $controllers[$name] = $manager->get($canonical);
            } catch (\Exception $e) {
                continue;
            }
        }

        $this->controllers = $controllers;
        return $controllers;
    }
}