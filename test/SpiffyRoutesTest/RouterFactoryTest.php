<?php

namespace SpiffyRoutesTest;

use SpiffyRoutes\RouteBuilder;
use SpiffyRoutes\RouterFactory;
use SpiffyTest\Framework\TestCase;
use Zend\Console\Request as ConsoleRequest;
use Zend\Http\Request as HttpRequest;
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
        $loader->setInvokableClass('Invokable\Controller\Test', 'SpiffyRoutesTest\Assets\TestConsoleController');
        $loader->setServiceLocator($this->getServiceManager());

        $builder = new RouteBuilder($loader);

        $sm = clone $this->getServiceManager();
        $sm->setAllowOverride(true);
        $sm->setService('SpiffyRoutes\RouteBuilder', $builder);
        $sm->setService('Request', new ConsoleRequest());

        $router   = $sm->get('Router');
        $expected = $router->getRoutes();

        /** @var \Zend\Mvc\Router\SimpleRouteStack $router */
        $router = $this->factory->createService($sm);
        $routes = $router->getRoutes();

        $this->assertEquals($expected, $routes);

        $sm->setService('Request', new HttpRequest());

        /** @var \Zend\Mvc\Router\SimpleRouteStack $router */
        $router = $this->factory->createService($sm);
        $routes = $router->getRoutes();

        $this->assertEquals($expected, $routes);
    }
}