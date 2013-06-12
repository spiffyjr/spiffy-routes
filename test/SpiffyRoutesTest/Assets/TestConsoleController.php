<?php

namespace SpiffyRoutesTest\Assets;

use SpiffyRoutes\Annotation as Route;
use Zend\Mvc\Controller\AbstractActionController;

class TestConsoleController extends AbstractActionController
{
    /**
     * @Route\Catchall("/index", name="home")
     */
    public function literalAction()
    {}
}