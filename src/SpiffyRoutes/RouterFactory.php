<?php

namespace SpiffyRoutes;

use Zend\Console\Request as ConsoleRequest;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RouterFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @throws Exception\RuntimeException
     * @return \Zend\Mvc\Router\RouteStackInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var \Zend\Mvc\Router\RouteStackInterface $router */
        $router  = $serviceLocator->get('Router');
        $request = $serviceLocator->get('Request');

        if ($request instanceof ConsoleRequest) {
            return $router;
        }

        /** @var \SpiffyRoutes\RouteBuilder $builder */
        $builder = $serviceLocator->get('SpiffyRoutes\RouteBuilder');

        $router->addRoutes($builder->getRouterConfig());
        return $router;
    }
}