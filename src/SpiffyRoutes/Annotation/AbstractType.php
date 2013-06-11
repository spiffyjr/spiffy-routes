<?php

namespace SpiffyRoutes\Annotation;

use Doctrine\Common\Annotations\Annotation;

abstract class AbstractType extends Annotation
{
    public $type;
}