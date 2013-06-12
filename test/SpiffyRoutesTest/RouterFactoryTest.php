<?php

namespace SpiffyRoutesTest;

use SpiffyRoutes\RouteBuilder;
use SpiffyRoutes\RouterFactory;
use SpiffyTest\Framework\TestCase;
use Zend\Console\Request;
use Zend\Mvc\Controller\ControllerManager;

class RouterFactoryTest extends TestCase
{
    /**
     * @var RouterFactory
     */
    protected $factory;

    public function setUp()
    {
        $this->factory = new RouterFactory();
    }

    public function testInstanceReturned()
    {
        $this->assertInstanceOf(
            'Zend\Mvc\Router\RouteStackInterface',
            $this->factory->createService($this->getServiceManager())
        );
    }

    public function testConsoleRequestsAreSkipped()
    {
        $loader  = new ControllerManager();
        $loader->setInvokableClass('Invokable\Controller\Test', 'SpiffyRoutesTest\Assets\TestController');
        $loader->setServiceLocator($this->getServiceManager());

        $builder = new RouteBuilder($loader);

        $sm = $this->getServiceManager();
        $sm->setAllowOverride(true);
        $sm->setService('SpiffyRoutes\RouteBuilder', $builder);
        $sm->setService('Request', new Request());

        /** @var \Zend\Mvc\Router\SimpleRouteStack $router */
        $router = $this->factory->createService($sm);
        $routes = $router->getRoutes();

        $this->assertCount(0, $routes);
    }
}