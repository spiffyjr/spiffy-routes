<?php

namespace SpiffyRoutes\Listener;

use ArrayObject;
use SpiffyRoutes\Annotation\AbstractType;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

class ActionAnnotationsListener implements ListenerAggregateInterface
{
    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * @var array
     */
    protected $canonicalNamesReplacements = array('-' => '_', ' ' => '_', '\\' => '_', '/' => '_');

    /**
     * {@inheritDoc}
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $callback) {
            if ($events->detach($callback)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('configureAction', array($this, 'configureDefaults'));
        $this->listeners[] = $events->attach('configureAction', array($this, 'configureRoute'));
        $this->listeners[] = $events->attach('configureAction', array($this, 'configureType'));
        $this->listeners[] = $events->attach('discoverName', array($this, 'discoverName'));
    }

    /**
     * @param EventInterface $event
     * @return string
     */
    public function discoverName(EventInterface $event)
    {
        $annotations = $event->getParam('annotations');

        foreach ($annotations as $annotation) {
            if ($annotation instanceof AbstractType && $annotation->name) {
                return $annotation->name;
            }
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
    public function configureDefaults(EventInterface $event)
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
    public function configureRoute(EventInterface $event)
    {
        $annotation = $event->getParam('annotation');
        if (!$annotation instanceof AbstractType) {
            return;
        }

        $controllerSpec = $event->getParam('controllerSpec');
        $actionSpec     = $event->getParam('actionSpec');

        $route = isset($controllerSpec['root']) ? $controllerSpec['root'] : '';
        $route.= $annotation->value;

        $actionSpec['options']['route'] = $route;
    }

    /**
     * @param EventInterface $event
     */
    public function configureType(EventInterface $event)
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