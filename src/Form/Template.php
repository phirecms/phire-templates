<?php
/**
 * Phire Templates Module
 *
 * @link       https://github.com/phirecms/phire-templates
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Templates\Form;

use Pop\Form\Form;
use Pop\Validator;
use Phire\Templates\Table;

/**
 * Template Form class
 *
 * @category   Phire\Templates
 * @package    Phire\Templates
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    1.0.0
 */
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
     * @return Template
     */
    public function __construct(array $fields, $action = null, $method = 'post')
    {
        parent::__construct($fields, $action, $method);
        $this->setAttribute('id', 'template-form');
        $this->setIndent('    ');
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @return Template
     */
    public function setFieldValues(array $values = null)
    {
        parent::setFieldValues($values);

        if (($_POST) && (null !== $this->name)) {
            // Check for dupe device
            if (($this->template_parent_id != '----') && ($this->device != '----')) {
                $parent   = Table\Templates::findBy(['id' => (int)$this->template_parent_id, 'device' => $this->device]);
                $children = Table\Templates::findBy([
                    'parent_id' => (int)$this->template_parent_id,
                    'device'    => $this->device,
                    'id !='     => $this->id
                ]);
                if (($parent->hasRows()) || ($children->hasRows())) {
                    $this->getElement('device')
                        ->addValidator(new Validator\NotEqual($this->device, 'That device has already been added for this template set.'));
                }
            }
        }

        return $this;
    }

}