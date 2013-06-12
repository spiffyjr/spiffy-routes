<?php

namespace SpiffyRoutesTest\Listener;

use SpiffyRoutes\Listener\ActionAnnotationsListener;
use Zend\EventManager\Event;

class ActionAnnotationsListnerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerEarlyReturns
     */
    public function testHandlesReturnEarlyWithNoAnnotation($method)
    {
        $listener = new ActionAnnotationsListener();
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
