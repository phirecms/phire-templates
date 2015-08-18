<?php

return [
    APP_URI => [
        '/templates[/]' => [
            'controller' => 'Phire\Templates\Controller\IndexController',
            'action'     => 'index',
            'acl'        => [
                'resource'   => 'templates',
                'permission' => 'index'
            ]
        ],
        '/templates/add[/]' => [
            'controller' => 'Phire\Templates\Controller\IndexController',
            'action'     => 'add',
            'acl'        => [
                'resource'   => 'templates',
                'permission' => 'add'
            ]
        ],
        '/templates/copy/:id' => [
            'controller' => 'Phire\Templates\Controller\IndexController',
            'action'     => 'copy',
            'acl'        => [
                'resource'   => 'templates',
                'permission' => 'copy'
            ]
        ],
        '/templates/edit/:id' => [
            'controller' => 'Phire\Templates\Controller\IndexController',
            'action'     => 'edit',
            'acl'        => [
                'resource'   => 'templates',
                'permission' => 'edit'
            ]
        ],
        '/templates/json/:id/:marked' => [
            'controller' => 'Phire\Templates\Controller\IndexController',
            'action'     => 'json',
            'acl'        => [
                'resource'   => 'templates',
                'permission' => 'json'
            ]
        ],
        '/templates/remove[/]' => [
            'controller' => 'Phire\Templates\Controller\IndexController',
            'action'     => 'remove',
            'acl'        => [
                'resource'   => 'templates',
                'permission' => 'remove'
            ]
        ]
    ]
];
