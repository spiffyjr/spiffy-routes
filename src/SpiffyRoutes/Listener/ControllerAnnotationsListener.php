<?php

namespace SpiffyRoutes\Listener;

use SpiffyRoutes\Annotation\Root;
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
        $this->listeners[] = $events->attach('configureController', array($this, 'handleRoot'));
    }

    /**
     * @param EventInterface $event
     */
    public function handleRoot(EventInterface $event)
    {
        $annotation = $event->getParam('annotation');
        if (!$annotation instanceof Root) {
            return;
        }

        $controllerSpec         = $event->getParam('controllerSpec');
        $controllerSpec['root'] = $annotation->value;
    }
}