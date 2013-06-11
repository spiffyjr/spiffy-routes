<?php

namespace SpiffyRoutes\Listener;

use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

class ControllerAnnotationsListener implements ListenerAggregateInterface
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
        $this->listeners[] = $events->attach('configureController', array($this, 'configureDefaults'));
    }

    public function configureDefaults(EventInterface $event)
    {
        $routeSpec                                      = $event->getParam('routeSpec');
        $routeSpec['options']['defaults']['controller'] = $event->getParam('name');
    }
}