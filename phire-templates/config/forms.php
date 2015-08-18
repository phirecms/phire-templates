<?php

return [
    'Phire\Templates\Form\Template' => [
        [
            'submit' => [
                'type'       => 'submit',
                'value'      => 'Save',
                'attributes' => [
                    'class'  => 'save-btn wide'
                ]
            ],
            'template_parent_id' => [
                'type'  => 'select',
                'label' => 'Parent',
                'value' => [
                    '----' => '----',
                ]
            ],
            'device' => [
                'type'  => 'select',
                'label' => 'Device',
                'value' => [
                    '----'           => '----',
                    'desktop'        => 'Desktop',
                    'mobile'         => 'Any Mobile Device',
                    'phone'          => 'Any Mobile Phone',
                    'tablet'         => 'Any Mobile Tablet',
                    'iphone'         => 'iPhone',
                    'ipad'           => 'iPad',
                    'android-phone'  => 'Android Phone',
                    'android-tablet' => 'Android Tablet',
                    'windows-phone'  => 'Windows Phone',
                    'windows-tablet' => 'Windows Tablet',
                    'blackberry'     => 'Blackberry',
                    'palm'           => 'Palm'
                ],
                'marked' => 'desktop'
            ],
            'id' => [
                'type'  => 'hidden',
                'value' => 0
            ]
        ],
        [
            'name' => [
                'type'       => 'text',
                'label'      => 'Name',
                'required'   => true,
                'attributes' => [
                    'size'   => 60,
                    'style'  => 'width: 99.5%'
                ]
            ],
            'template_source' => [
                'type'  => 'textarea',
                'label' => 'Template',
                'attributes' => [
                    'rows'  => 30,
                    'cols'  => 80,
                    'style' => 'width: 100%'
                ]
            ]
        ]
    ]
];
