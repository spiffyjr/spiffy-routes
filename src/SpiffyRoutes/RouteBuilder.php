<?php

namespace SpiffyRoutes;

use ArrayObject;
use ReflectionClass;
use SpiffyRoutes\Listener\ActionAnnotationsListener;
use SpiffyRoutes\Listener\ControllerAnnotationsListener;
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
     * Builds the router config from controller/action annotations.
     */
    public function getRouterConfig()
    {
        $annotationManager = $this->getAnnotationManager();
        $controllers       = $this->generateControllerList();
        $routerConfig      = array();

        foreach ($controllers as $controllerName => $controller) {
            $routeSpec   = new ArrayObject();
            $reflection  = new ClassReflection($controller);
            $annotations = $reflection->getAnnotations($annotationManager);

            if ($annotations instanceof AnnotationCollection) {
                $this->configureController($controllerName, $annotations, $routeSpec);
            }

            foreach ($reflection->getMethods() as $method) {
                $annotations = $method->getAnnotations($annotationManager);

                if ($annotations instanceof AnnotationCollection) {
                    $this->configureAction($annotations, $method, $routeSpec);
                }
            }

            if (0 !== count($routeSpec)) {
                $routerConfig[] = $routeSpec;
            }
        }
        return $routerConfig;
    }

    /**
     * @param $controllerName
     * @param $annotations
     * @param $routeSpec
     */
    protected function configureController($controllerName, $annotations, $routeSpec)
    {
        $eventManager = $this->getEventManager();
        foreach ($annotations as $annotation) {
            $eventManager->trigger(__FUNCTION__, $this, array(
                'annotation' => $annotation,
                'name'       => $controllerName,
                'routeSpec'  => $routeSpec
            ));
        }
    }

    /**
     * @param $annotations
     * @param MethodReflection $method
     * @param $routeSpec
     */
    protected function configureAction($annotations, MethodReflection $method, $routeSpec)
    {
        if ($this->checkForExclude($annotations)) {
            return;
        }

        $eventManager = $this->getEventManager();
        foreach ($annotations as $annotation) {
            $eventManager->trigger(__FUNCTION__, $this, array(
                'annotation'       => $annotation,
                'name'             => $method->getName(),
                'routeSpec'        => $routeSpec
            ));
        }
    }

    /**
     * @param $annotations
     * @return bool
     */
    protected function checkForExclude($annotations)
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

        $manager    = $this->controllerManager;
        $services   = $manager->getRegisteredServices();
        $canonical  = $manager->getCanonicalNames();
        $reflection = new ReflectionClass($manager);
        $property   = $reflection->getProperty('invokableClasses');
        $property->setAccessible(true);

        $controllers = array();
        foreach ($property->getValue($manager) as $name => $controller) {
            $controllers[array_search($name, $canonical)] = $controller;
        }

        foreach ($services['factories'] as $factory) {
            try {
                $controllers[array_search($factory, $canonical)] = $manager->get($factory);
            } catch (\Exception $e) {
                continue;
            }
        }

        $this->controllers = $controllers;
        return $controllers;
    }
}