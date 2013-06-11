<?php

namespace SpiffyRoutes\Factory;

use SpiffyRoutes\RouteBuilder;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RouteBuilderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return RouteBuilder
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var \Zend\Mvc\Controller\ControllerManager $controllerManager */
        $controllerManager = $serviceLocator->get('ControllerLoader');
        return new RouteBuilder($controllerManager);
    }
}