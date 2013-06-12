<?php

namespace SpiffyRoutesTest\Assets;

use SpiffyRoutes\Annotation as Route;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * @Route\Root("/root")
 */
class TestRootController extends AbstractActionController
{
    /**
     * @Route\Literal("/index", name="home")
     */
    public function literalAction()
    {}
}