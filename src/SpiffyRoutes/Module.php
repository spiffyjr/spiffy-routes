<?php

namespace SpiffyRoutes;

use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

class Module implements
    BootstrapListenerInterface,
    ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function onBootstrap(EventInterface $e)
    {
        /** @var \Zend\Mvc\MvcEvent $e */
        $app = $e->getApplication();
        $sm  = $app->getServiceManager();

        $sm->get('Router')->addRoutes($sm->get('SpiffyRoutes\RouteBuilder')->getRouterConfig());
    }

    /**
     * {@inheritDoc}
     */
    public function getServiceConfig()
    {
        return include __DIR__ . '/../../config/service.config.php';
    }
}