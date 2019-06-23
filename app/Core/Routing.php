<?php
namespace Core;

class Routing{
    public $routes = [
        [
            'route' => '',
            'module' => 'Base',
            'controller' => 'DefaultController',
            'action' => 'homepage'
        ],
        [
            'route' => 'default/homepage',
            'module' => 'Base',
            'controller' => 'DefaultController',
            'action' => 'homepage'
        ],
        [
            'route' => 'default/about',
            'module' => 'Base',
            'controller' => 'DefaultController',
            'action' => 'about'
        ],
        [
            'route' => 'default/users',
            'module' => 'Base',
            'controller' => 'DefaultController',
            'action' => 'users'
        ]
    ];
}