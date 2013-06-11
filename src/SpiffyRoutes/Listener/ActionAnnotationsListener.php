<?php

namespace SpiffyRoutes\Listener;

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
    }

    public function configureDefaults(EventInterface $event)
    {
        $name    = $event->getParam('name');
        $matches = array();

        if (!preg_match('/^(.*)Action$/', $name, $matches)) {
            return;
        }

        $routeSpec                                  = $event->getParam('routeSpec');
        $routeSpec['options']['defaults']['action'] = $matches[1];
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

        $routeSpec                     = $event->getParam('routeSpec');
        $routeSpec['options']['route'] = $annotation->value;
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

        $routeSpec         = $event->getParam('routeSpec');
        $routeSpec['type'] = $annotation->type;
    }
}