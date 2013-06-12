<?php

namespace SpiffyRoutes;

use SpiffyRoutes\RouteBuilder;
use Zend\Cache\Storage\StorageInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RouteBuilderFactory implements FactoryInterface
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
        /** @var \SpiffyRoutes\Options\ModuleOptions $options */
        $options = $serviceLocator->get('SpiffyRoutes\Options\ModuleOptions');

        /** @var \Zend\Mvc\Controller\ControllerManager $loader */
        $loader  = $serviceLocator->get('ControllerLoader');
        $service = new RouteBuilder($loader);

        $adapter = $options->getCacheAdapter();
        if (is_string($adapter)) {
            if ($serviceLocator->has($adapter)) {
                $adapter = $serviceLocator->get($adapter);
            } else {
                $adapter = new $adapter();
            }
        }

        if (!$adapter instanceof StorageInterface) {
            throw new Exception\RuntimeException('Invalid cache storage adapter');
        }

        $service->setCacheAdapter($adapter);

        return $service;
    }
}