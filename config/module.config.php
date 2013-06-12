<?php

return array(
    'console' => array(
        'router' => array(
            'routes' => array(
                'spiffy_routes_build' => array(
                    'options' => array(
                        'route'    => 'spiffyroutes build',
                        'defaults' => array(
                            'controller' => 'SpiffyRoutes\Controller\CliController',
                            'action'     => 'build'
                        )
                    )
                ),
                'spiffy_routes_clear' => array(
                    'options' => array(
                        'route'    => 'spiffyroutes clear',
                        'defaults' => array(
                            'controller' => 'SpiffyRoutes\Controller\CliController',
                            'action'     => 'clear'
                        )
                    )
                )
            )
        )
    ),
);