<?php

namespace SpiffyRoutes;

use Zend\Cache\Storage\Adapter\Filesystem;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CacheFactory implements FactoryInterface
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
        if (!file_exists('data/spiffy-routes')) {
            mkdir('data/spiffy-routes');
        }

        $cache = new Filesystem();
        $cache->getOptions()->setCacheDir('data/spiffy-routes');

        return $cache;
    }
}