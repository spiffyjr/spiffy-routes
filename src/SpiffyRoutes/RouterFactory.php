<?php

namespace SpiffyRoutes;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RouterFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @throws Exception\RuntimeException
     * @return RouteBuilder
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var \Zend\Mvc\Router\RouteStackInterface $router */
        $router = $serviceLocator->get('Router');

        /** @var \SpiffyRoutes\RouteBuilder $builder */
        $builder = $serviceLocator->get('SpiffyRoutes\RouteBuilder');

        $router->addRoutes($builder->getRouterConfig());
        return $router;
    }
}