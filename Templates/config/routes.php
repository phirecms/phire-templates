<?php

return [
    APP_URI => [
        '/templates[/]' => [
            'controller' => 'Templates\Controller\IndexController',
            'action'     => 'index',
            'acl'        => [
                'resource'   => 'template',
                'permission' => 'index'
            ]
        ],
        '/templates/add[/]' => [
            'controller' => 'Templates\Controller\IndexController',
            'action'     => 'add',
            'acl'        => [
                'resource'   => 'template',
                'permission' => 'add'
            ]
        ],
        '/templates/edit/:id' => [
            'controller' => 'Templates\Controller\IndexController',
            'action'     => 'edit',
            'acl'        => [
                'resource'   => 'template',
                'permission' => 'edit'
            ]
        ],
        '/templates/remove[/]' => [
            'controller' => 'Templates\Controller\IndexController',
            'action'     => 'remove',
            'acl'        => [
                'resource'   => 'template',
                'permission' => 'remove'
            ]
        ]
    ]
];
