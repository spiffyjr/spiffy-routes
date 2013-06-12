<?php

namespace SpiffyRoutes;

use ArrayObject;
use SpiffyRoutes\Listener\ActionAnnotationsListener;
use SpiffyRoutes\Listener\ControllerAnnotationsListener;
use Zend\Cache\Storage\Adapter\Memory as MemoryCache;
use Zend\Cache\Storage\StorageInterface;
use Zend\Code\Annotation\AnnotationCollection;
use Zend\Code\Annotation\AnnotationInterface;
use Zend\Code\Annotation\AnnotationManager;
use Zend\Code\Annotation\Parser;
use Zend\Code\Reflection\ClassReflection;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\Controller\ControllerManager;

class RouteBuilder
{
    const CACHE_KEY = 'spiffy-routes';

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
        'SpiffyRoutes\\Annotation\\Regex',
        'SpiffyRoutes\\Annotation\\Root',
        'SpiffyRoutes\\Annotation\\Segment',
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
     * @var array
     */
    protected $routerConfig = array();

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
    public function setCacheAdapter(StorageInterface $cacheAdapter)
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
            $this->cacheAdapter = new MemoryCache();
        }
        return $this->cacheAdapter;
    }

    /**
     * Builds the router config from controller/action annotations.
     */
    public function getRouterConfig()
    {
        if ($this->routerConfig) {
            return $this->routerConfig;
        }

        $cache  = $this->getCacheAdapter();
        $config = $cache->getItem(static::CACHE_KEY);

        if ($config) {
            $this->routerConfig = unserialize($config);
            return $this->routerConfig;
        }

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
                $actionName = $this->getActionName($method->getName());
                if (!$actionName) {
                    continue;
                }

                $annotations = $method->getAnnotations($annotationManager);

                if (!$annotations instanceof AnnotationCollection) {
                    continue;
                }

                foreach ($annotations as $annotation) {
                    $actionSpec         = new ArrayObject();
                    $actionSpec['name'] = $actionName;

                    $this->configureAction($annotation, $controllerSpec, $actionSpec);

                    if (isset($actionSpec['type'])) {
                        $routeName = $this->discoverName($annotation, $controllerSpec, $actionSpec);
                        if ($routeName) {
                            $routerConfig[$routeName] = $actionSpec->getArrayCopy();
                        }
                    }
                }
            }
        }

        $cache->setItem(static::CACHE_KEY, serialize($routerConfig));
        $this->routerConfig = $routerConfig;
        return $this->routerConfig;
    }

    /**
     * Reset to clean state.
     */
    public function reset()
    {
        $this->cacheAdapter = null;
        $this->routerConfig = null;
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
     * @param $annotation
     * @param ArrayObject $controllerSpec
     * @param ArrayObject $actionSpec
     * @return mixed|null
     */
    protected function discoverName(
        $annotation,
        ArrayObject $controllerSpec,
        ArrayObject $actionSpec
    ) {
        $results = $this->getEventManager()->trigger(__FUNCTION__, $this, array(
            'annotation'    => $annotation,
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
     * @param $annotation
     * @param ArrayObject $controllerSpec
     * @param ArrayObject $actionSpec
     */
    protected function configureAction(
        $annotation,
        ArrayObject $controllerSpec,
        ArrayObject $actionSpec
    ) {
        if ($this->checkForExclude($annotation)) {
            return;
        }

        $eventManager = $this->getEventManager();
        $eventManager->trigger(__FUNCTION__, $this, array(
            'annotation'     => $annotation,
            'controllerSpec' => $controllerSpec,
            'actionSpec'     => $actionSpec
        ));
    }

    /**
     * @param $annotation
     * @return bool
     */
    protected function checkForExclude($annotation)
    {
        $results = $this->getEventManager()->trigger(__FUNCTION__, $this, array(
            'annotation' => $annotation,
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