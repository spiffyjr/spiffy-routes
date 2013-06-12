<?php

return array(
    'factories' => array(
        'SpiffyRoutes\Cache'                 => 'SpiffyRoutes\CacheFactory',
        'SpiffyRoutes\Router'                => 'SpiffyRoutes\RouterFactory',
        'SpiffyRoutes\RouteBuilder'          => 'SpiffyRoutes\RouteBuilderFactory',
        'SpiffyRoutes\Options\ModuleOptions' => 'SpiffyRoutes\Options\ModuleOptionsFactory'
    ),
);