<?php

namespace SpiffyRoutesTest;

use SpiffyRoutes\Options\ModuleOptions;
use SpiffyRoutes\RouteBuilderFactory;
use SpiffyTest\Framework\TestCase;

class RouteBuilderFactoryTest extends TestCase
{
    /**
     * @var RouteBuilderFactory
     */
    protected $factory;

    public function setUp()
    {
        $this->factory = new RouteBuilderFactory();
    }

    public function testInstanceReturned()
    {
        $this->assertInstanceOf(
            'SpiffyRoutes\RouteBuilder',
            $this->factory->createService($this->getServiceManager())
        );
    }

    public function testSettingCacheAdapter()
    {
        $this->getServiceManager()->setAllowOverride(true);
        $service = $this->factory->createService($this->getServiceManager());
        $this->assertInstanceOf('Zend\Cache\Storage\Adapter\Memory', $service->getCacheAdapter());

        $options = new ModuleOptions();
        $options->setCacheAdapter('Zend\Cache\Storage\Adapter\Filesystem');

        $this->getServiceManager()->setService('SpiffyRoutes\Options\ModuleOptions', $options);
        $service = $this->factory->createService($this->getServiceManager());
        $this->assertInstanceOf('Zend\Cache\Storage\Adapter\Filesystem', $service->getCacheAdapter());
    }
}