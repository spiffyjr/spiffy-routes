<?php

namespace SpiffyRoutes;

use SpiffyTest\Framework\TestCase;
use Zend\Code\Annotation\AnnotationManager;
use Zend\Mvc\Controller\ControllerManager;

class RouteBuilderTest extends TestCase
{
    /**
     * @var \SpiffyRoutes\RouteBuilder
     */
    protected $builder;

    public function setUp()
    {
        $this->builder = $this->getServiceManager()->get('SpiffyRoutes\RouteBuilder');
    }

    public function testAnnotationsLazyLoaded()
    {
        $this->assertInstanceOf(
            'Zend\Code\Annotation\AnnotationManager',
            $this->builder->getAnnotationManager()
        );
    }

    public function testControllersWithServiceManagerExceptionsAreSkipped()
    {
        $loader = new ControllerManager();
        $loader->setFactory('Exception', function() {
            return null;
        });

        $builder = new RouteBuilder($loader);
        $this->assertTrue(is_array($builder->getRouterConfig()));
    }

    public function testNameCreatedFromControllerAndActionIfNotSpecified()
    {
        $loader  = $this->getLoader();
        $builder = new RouteBuilder($loader);

        $config = $builder->getRouterConfig();
        $this->assertExpectedConfig($config);
        $this->assertCount(1, $config);
        $this->assertArrayHasKey('invokable_controller_test_literal', $config);
    }

    public function testRouterConfigFromInvokables()
    {
        $loader  = $this->getLoader();
        $builder = new RouteBuilder($loader);

        $config = $builder->getRouterConfig();
        $this->assertExpectedConfig($config);
        $this->assertCount(1, $config);
    }

    public function testRouterConfigFromFactories()
    {
        $loader  = $this->getLoader();
        $loader->setFactory('Factory\Controller\Test', function() {
            return new \SpiffyRoutesTest\Assets\TestController();
        });
        $builder = new RouteBuilder($loader);

        $config = $builder->getRouterConfig();
        $this->assertExpectedConfig($config);
        $this->assertCount(2, $config);
    }

    public function testRoutesWithRootFromController()
    {
        $loader  = $this->getLoader();
        $loader->setInvokableClass('Controller', 'SpiffyRoutesTest\Assets\TestRootController');
        $builder = new RouteBuilder($loader);

        $config = $builder->getRouterConfig();
        $this->assertExpectedConfig($config);
        $this->assertEquals('/root/index', $config['home']['options']['route']);
        $this->assertCount(2, $config);
    }

    /**
     * @param array $routeConfig
     */
    protected function assertExpectedConfig(array $routeConfig)
    {
        foreach ($routeConfig as $name => $config) {
            $this->assertTrue(is_string($name));
            $this->assertArrayHasKey('type', $config);
            $this->assertArrayHasKey('options', $config);
            $this->assertArrayHasKey('defaults', $config['options']);
            $this->assertArrayHasKey('action', $config['options']['defaults']);
            $this->assertArrayHasKey('controller', $config['options']['defaults']);
        }
    }

    /**
     * @return ControllerManager
     */
    protected function getLoader()
    {
        $loader = new ControllerManager();
        $loader->setInvokableClass('Invokable\Controller\Test', 'SpiffyRoutesTest\Assets\TestController');
        $loader->setServiceLocator($this->getServiceManager());
        $loader->setRetrieveFromPeeringManagerFirst(false);

        return $loader;
    }
}