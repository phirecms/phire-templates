<?php

namespace Templates\Form;

use Pop\Form\Form;
use Pop\Validator;

class Template extends Form
{

    /**
     * Constructor
     *
     * Instantiate the form object
     *
     * @param  array  $fields
     * @param  string $action
     * @param  string $method
     * @return Media
     */
    public function __construct(array $fields, $action = null, $method = 'post')
    {
        parent::__construct($fields, $action, $method);
        $this->setAttribute('id', 'template-form');
        $this->setIndent('    ');
    }

}