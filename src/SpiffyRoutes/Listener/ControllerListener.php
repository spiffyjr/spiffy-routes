<?php

namespace SpiffyRoutes\Listener;

use SpiffyRoutes\Annotation\Root;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

class ControllerListener extends AbstractListenerAggregate
{
    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('configureController', array($this, 'handleRoot'));
        $this->listeners[] = $events->attach('checkForExcludeController', array($this, 'checkForExclude'));
    }

    /**
     * @param EventInterface $event
     * @return bool
     */
    public function checkForExclude(EventInterface $event)
    {
        /** @var \SpiffyRoutes\RouteBuilder $builder */
        $builder  = $event->getTarget();
        $spec     = $event->getParam('controllerSpec');
        $excluded = $builder->getOptions()->getExcluded();

        return in_array($spec['name'], $excluded);
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