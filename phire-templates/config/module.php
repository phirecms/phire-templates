<?php
/**
 * Module Name: phire-templates
 * Author: Nick Sagona
 * Description: This is the templates module for Phire CMS 2, to be used in conjunction with the Content module
 * Version: 1.0
 */
return [
    'phire-templates' => [
        'prefix'     => 'Phire\Templates\\',
        'src'        => __DIR__ . '/../src',
        'routes'     => include 'routes.php',
        'resources'  => include 'resources.php',
        'forms'      => include 'forms.php',
        'nav.phire'  => [
            'templates' => [
                'name' => 'Templates',
                'href' => '/templates',
                'acl' => [
                    'resource'   => 'templates',
                    'permission' => 'index'
                ],
                'attributes' => [
                    'class' => 'templates-nav-icon'
                ]
            ]
        ],
        'models' => [
            'Phire\Templates\Model\Template' => []
        ],
        'uninstall' => function() {
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/templates')) {
                $dir = new \Pop\File\Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/templates');
                $dir->emptyDir(true);
            }
        },
        'events' => [
            [
                'name'     => 'app.route.pre',
                'action'   => 'Phire\Templates\Event\Template::bootstrap',
                'priority' => 1000
            ],
            [
                'name'     => 'app.send.pre',
                'action'   => 'Phire\Templates\Event\Template::setTemplate',
                'priority' => 10000
            ],
            [
                'name'     => 'app.send.pre',
                'action'   => 'Phire\Templates\Event\Template::init',
                'priority' => 1000
            ],
            [
                'name'     => 'app.send.post',
                'action'   => 'Phire\Templates\Event\Template::parseBody',
                'priority' => 10000
            ]
        ],
        'history' => 10
    ]
];
