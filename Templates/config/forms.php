<?php

return [
    'Templates\Form\Template' => [
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
                    'Desktop' => 'Desktop'
                ]
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
                    'rows'  => 20,
                    'cols'  => 80,
                    'style' => 'width: 99.5%'
                ]
            ]
        ]
    ]
];
