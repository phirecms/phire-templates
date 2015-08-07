<?php

return [
    APP_URI => [
        '/templates[/]' => [
            'controller' => 'Templates\Controller\IndexController',
            'action'     => 'index',
            'acl'        => [
                'resource'   => 'templates',
                'permission' => 'index'
            ]
        ],
        '/templates/add[/]' => [
            'controller' => 'Templates\Controller\IndexController',
            'action'     => 'add',
            'acl'        => [
                'resource'   => 'templates',
                'permission' => 'add'
            ]
        ],
        '/templates/edit/:id' => [
            'controller' => 'Templates\Controller\IndexController',
            'action'     => 'edit',
            'acl'        => [
                'resource'   => 'templates',
                'permission' => 'edit'
            ]
        ],
        '/templates/remove[/]' => [
            'controller' => 'Templates\Controller\IndexController',
            'action'     => 'remove',
            'acl'        => [
                'resource'   => 'templates',
                'permission' => 'remove'
            ]
        ]
    ]
];
