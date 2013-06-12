<?php

namespace SpiffyRoutesTest;

use SpiffyRoutes\Options\ModuleOptionsFactory;
use SpiffyTest\Framework\TestCase;

class ModuleOptionsFactoryTest extends TestCase
{
    /**
     * @var ModuleOptionsFactory
     */
    protected $factory;

    public function setUp()
    {
        $this->factory = new ModuleOptionsFactory();
    }

    public function testInstanceReturned()
    {
        $this->assertInstanceOf(
            'SpiffyRoutes\Options\ModuleOptions',
            $this->factory->createService($this->getServiceManager())
        );
    }
}