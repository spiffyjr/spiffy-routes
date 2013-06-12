<?php

namespace SpiffyRoutes\Annotation;

use Doctrine\Common\Annotations\Annotation;

abstract class AbstractType extends Annotation
{
    /**
     * Key used to denote what the 'route' key is.
     *
     * @var string
     */
    public $routeKey = 'route';

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $type;
}