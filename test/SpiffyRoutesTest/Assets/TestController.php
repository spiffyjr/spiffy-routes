<?php

namespace SpiffyRoutesTest\Assets;

use SpiffyRoutes\Annotation as Route;
use Zend\Mvc\Controller\AbstractActionController;

class TestController extends AbstractActionController
{
    /**
     * @Route\Literal("/index")
     */
    public function literalAction()
    {}

}