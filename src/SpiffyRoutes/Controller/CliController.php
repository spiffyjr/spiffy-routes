<?php

namespace SpiffyRoutes\Controller;

use SpiffyRoutes\Exception;
use SpiffyRoutes\RouteBuilder;
use Zend\Console\ColorInterface;
use Zend\Mvc\Controller\AbstractActionController;

class CliController extends AbstractActionController
{
    /**
     * @var RouteBuilder
     */
    protected $routeBuilder;

    /**
     * @throws \SpiffyRoutes\Exception\RuntimeException
     */
    public function buildAction()
    {
        /** @var \Zend\Console\Adapter\AdapterInterface $console */
        $console = $this->getServiceLocator()->get('console');
        $console->writeLine('building cache, please wait...', ColorInterface::YELLOW);

        $builder = $this->getRouteBuilder();
        $builder->getCacheAdapter()->removeItem(RouteBuilder::CACHE_KEY);
        $builder->getRouterConfig();

        $console->writeLine('success!', ColorInterface::GREEN);
    }

    /**
     * @throws \SpiffyRoutes\Exception\RuntimeException
     */
    public function clearAction()
    {
        /** @var \Zend\Console\Adapter\AdapterInterface $console */
        $console = $this->getServiceLocator()->get('console');
        $console->writeLine('clearing cache, please wait...', ColorInterface::YELLOW);

        $builder = $this->getRouteBuilder();
        $builder->getCacheAdapter()->removeItem(RouteBuilder::CACHE_KEY);

        $console->writeLine('success!', ColorInterface::GREEN);
    }

    /**
     * @param \SpiffyRoutes\RouteBuilder $routeBuilder
     * @return CliController
     */
    public function setRouteBuilder($routeBuilder)
    {
        $this->routeBuilder = $routeBuilder;
        return $this;
    }

    /**
     * @return \SpiffyRoutes\RouteBuilder
     */
    public function getRouteBuilder()
    {
        if (!$this->routeBuilder instanceof RouteBuilder) {
            $this->routeBuilder = $this->getServiceLocator()->get('SpiffyRoutes\RouteBuilder');
        }
        return $this->routeBuilder;
    }
}