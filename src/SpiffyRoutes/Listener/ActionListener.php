<?php

namespace SpiffyRoutes\Listener;

use SpiffyRoutes\Annotation\AbstractType;
use SpiffyRoutes\Options\ModuleOptions;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

class ActionListener extends AbstractListenerAggregate
{
    /**
     * @var array
     */
    protected $canonicalNamesReplacements = array('-' => '_', ' ' => '_', '\\' => '_', '/' => '_');

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('configureAction', array($this, 'handleDefaults'));
        $this->listeners[] = $events->attach('configureAction', array($this, 'handleExtras'));
        $this->listeners[] = $events->attach('configureAction', array($this, 'handleRoute'));
        $this->listeners[] = $events->attach('configureAction', array($this, 'handleType'));
        $this->listeners[] = $events->attach('discoverName', array($this, 'discoverName'));
    }

    /**
     * @param EventInterface $event
     * @return string
     */
    public function discoverName(EventInterface $event)
    {
        $annotation = $event->getParam('annotation');
        if ($annotation instanceof AbstractType && $annotation->name) {
            return $annotation->name;
        }

        $controllerSpec = $event->getParam('controllerSpec');
        $actionSpec     = $event->getParam('actionSpec');

        $parts = array(
            $this->canonicalize($controllerSpec['name']),
            $this->canonicalize($actionSpec['name'])
        );

        return implode('_', $parts);
    }

    /**
     * @param EventInterface $event
     */
    public function handleDefaults(EventInterface $event)
    {
        $actionSpec     = $event->getParam('actionSpec');
        $controllerSpec = $event->getParam('controllerSpec');

        $defaults = array(
            'controller' => $controllerSpec['name'],
            'action'     => $actionSpec['name']
        );

        $actionSpec['options']['defaults'] = $defaults;
    }

    /**
     * @param EventInterface $event
     */
    public function handleExtras(EventInterface $event)
    {
        $annotation = $event->getParam('annotation');
        if (!$annotation instanceof AbstractType) {
            return;
        }

        $skip       = array('name', 'routeKey', $annotation->routeKey, 'type', 'value');
        $actionSpec = $event->getParam('actionSpec');
        foreach ($annotation as $key => $value) {
            if (in_array($key, $skip)) {
                continue;
            }
            $actionSpec['options'][$key] = $value;
        }
    }

    /**
     * @param EventInterface $event
     */
    public function handleRoute(EventInterface $event)
    {
        $annotation = $event->getParam('annotation');
        if (!$annotation instanceof AbstractType) {
            return;
        }

        $controllerSpec = $event->getParam('controllerSpec');
        $actionSpec     = $event->getParam('actionSpec');

        $route = isset($controllerSpec['root']) ? $controllerSpec['root'] : '';
        $route.= $annotation->value;

        $actionSpec['options'][$annotation->routeKey] = $route;
    }

    /**
     * @param EventInterface $event
     */
    public function handleType(EventInterface $event)
    {
        $annotation = $event->getParam('annotation');
        if (!$annotation instanceof AbstractType) {
            return;
        }

        $actionSpec         = $event->getParam('actionSpec');
        $actionSpec['type'] = $annotation->type;
    }

    protected function canonicalize($name)
    {
        return strtolower(strtr($name, $this->canonicalNamesReplacements));
    }
}