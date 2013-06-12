<?php

namespace SpiffyRoutes\Annotation;

/**
 * @Annotation
 */
class Regex extends AbstractType
{
    /**
     * @var string
     */
    public $routeKey = 'regex';

    /**
     * @var string
     */
    public $type = 'regex';

    /**
     * @var string
     */
    public $spec;
}