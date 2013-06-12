<?php

namespace SpiffyRoutes\Listener;

use SpiffyRoutes\Annotation\Root;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

class ControllerAnnotationsListener extends AbstractListenerAggregate
{
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