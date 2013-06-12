<?php

namespace SpiffyRoutes;

use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

class Module implements
    AutoloaderProviderInterface,
    BootstrapListenerInterface,
    ConfigProviderInterface,
    ConsoleUsageProviderInterface,
    ControllerProviderInterface,
    ServiceProviderInterface
{
    /**
     * Listen to the bootstrap event
     *
     * @param EventInterface $e
     * @return array
     */
    public function onBootstrap(EventInterface $e)
    {
        /** @var \Zend\Mvc\MvcEvent $e */
        $app = $e->getApplication();
        $sm  = $app->getServiceManager();

        // fire the factory to build the routes
        $sm->get('SpiffyRoutes\Router');
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * {@inheritDoc}
     */
    public function getControllerConfig()
    {
        return include __DIR__ . '/../../config/controller.config.php';
    }

    /**
     * {@inheritDoc}
     */
    public function getServiceConfig()
    {
        return include __DIR__ . '/../../config/service.config.php';
    }

    /**
     * {@inheritDoc}
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getConsoleUsage(AdapterInterface $console)
    {
        return array(
            $console->colorize('Usage:', ColorInterface::YELLOW),
            '  [options] command [arguments]',
            '',
            $console->colorize('Available Commands:', ColorInterface::YELLOW),
            array($console->colorize('  spiffyroutes build', ColorInterface::GREEN), 'build, or rebuild if present, the cache'),
            array($console->colorize('  spiffyroutes clear', ColorInterface::GREEN), 'clear the cache'),
        );
    }
}