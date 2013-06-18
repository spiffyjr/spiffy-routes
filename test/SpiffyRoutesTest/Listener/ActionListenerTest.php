<?php

namespace SpiffyRoutesTest\Listener;

use SpiffyRoutes\Listener\ActionListener;
use Zend\EventManager\Event;

class ActionListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerEarlyReturns
     */
    public function testHandlesReturnEarlyWithNoAnnotation($method)
    {
        $listener = new ActionListener();
        $event    = new Event();

        $listener->{$method}($event);
        $this->assertNull($event->getParam('actionSpec'));
    }

    public function providerEarlyReturns()
    {
        return array(
            array('handleExtras'),
            array('handleRoute'),
            array('handleType'),
        );
    }
}
