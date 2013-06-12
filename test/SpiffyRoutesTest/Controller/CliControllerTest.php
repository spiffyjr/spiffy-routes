<?php

namespace SpiffyRoutesTest\Controller;

use SpiffyRoutes\RouteBuilder;
use SpiffyTest\Controller\AbstractHttpControllerTestCase;

class CliControllerTest extends AbstractHttpControllerTestCase
{
    public function testBuildAction()
    {
        $this->setUseConsoleRequest(true);
        $this->dispatch('spiffyroutes build');
        $this->assertNotNull($this->getApplication()->getMvcEvent()->getRouteMatch());
        $this->assertControllerName('SpiffyRoutes\Controller\CliController');
        $this->assertActionName('build');

        $builder = $this->getApplicationServiceLocator()->get('SpiffyRoutes\RouteBuilder');
        $cache   = $builder->getCacheAdapter();

        $this->assertNotNull($cache->getItem(RouteBuilder::CACHE_KEY));
    }

    public function testClearAction()
    {
        $this->setUseConsoleRequest(true);
        $this->dispatch('spiffyroutes clear');
        $this->assertNotNull($this->getApplication()->getMvcEvent()->getRouteMatch());
        $this->assertControllerName('SpiffyRoutes\Controller\CliController');
        $this->assertActionName('clear');

        $builder = $this->getApplicationServiceLocator()->get('SpiffyRoutes\RouteBuilder');
        $cache   = $builder->getCacheAdapter();

        $this->assertNull($cache->getItem(RouteBuilder::CACHE_KEY));
    }
}