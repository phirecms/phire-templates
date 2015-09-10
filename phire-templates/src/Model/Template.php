<?php

namespace Phire\Templates\Model;

use Phire\Templates\Table;
use Phire\Model\AbstractModel;

class Template extends AbstractModel
{

    /**
     * Get all templates
     *
     * @param  string $sort
     * @return array
     */
    public function getAll($sort = null)
    {
        $order = (null !== $sort) ? $this->getSortOrder($sort) : 'id ASC';

        $templatesAry = [];
        $templates    = Table\Templates::findBy(['parent_id' => null], ['order' => $order]);

        foreach ($templates->rows() as $template) {
            $templatesAry[] = $template;
            $children = Table\Templates::findBy(['parent_id' => $template->id], ['order' => $order]);
            foreach ($children->rows() as $child) {
                $child->name = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&gt; ' . $child->name;
                $templatesAry[] = $child;
            }
        }

        return $templatesAry;
    }

    /**
     * Get template by ID
     *
     * @param  int $id
     * @return void
     */
    public function getById($id)
    {
        $template = Table\Templates::findById($id);
        if (isset($template->id)) {
            $data = $template->getColumns();
            $data['template_parent_id'] = $data['parent_id'];
            $data['template_source']    = $data['template'];
            unset($data['parent_id']);
            unset($data['template']);
            $this->data = array_merge($this->data, $data);
        }
    }

    /**
     * Get template
     *
     * @param  mixed $template
     * @return string
     */
    public function getTemplate($template)
    {
        $tmpl     = null;
        $template = (is_numeric($template)) ? Table\Templates::findById($template) : Table\Templates::findBy(['name' => $template]);

        if (isset($template->id)) {
            $mobile = (isset($_GET['mobile']) ? $_GET['mobile'] : null);
            $device = \Phire\Templates\Event\Template::getDevice($mobile);
            if ((null !== $device) && ($template->device != $device)) {
                $childTemplate = Table\Templates::findBy(['parent_id' => $template->id, 'device' => $device]);
                if (isset($childTemplate->id)) {
                    $tmpl = $childTemplate->template;
                } else {
                    $tmpl = $template->template;
                }
            } else {
                $tmpl = $template->template;
            }
            $tmpl = \Phire\Templates\Event\Template::parse($tmpl);
        }

        return $tmpl;
    }

    /**
     * Save new template
     *
     * @param  array $fields
     * @return void
     */
    public function save(array $fields)
    {
        $template = new Table\Templates([
            'parent_id' => ((isset($fields['template_parent_id']) && ($fields['template_parent_id'] != '----')) ?
                (int)$fields['template_parent_id'] : null),
            'name'      => $fields['name'],
            'device'    => ((isset($fields['device']) && ($fields['device'] != '----')) ? $fields['device'] : null),
            'template'  => (isset($fields['template_source']) ? $fields['template_source'] : null),
            'history'   => null,
            'visible'   => (int)$fields['visible']
        ]);
        $template->save();

        $this->data = array_merge($this->data, $template->getColumns());
    }

    /**
     * Update an existing template
     *
     * @param  array $fields
     * @param  int   $historyLimit
     * @return void
     */
    public function update(array $fields, $historyLimit)
    {
        $template = Table\Templates::findById((int)$fields['id']);
        if (isset($template->id)) {
            if ($fields['template_source'] != $template->template) {
                if (null !== $template->history) {
                    $history = json_decode($template->history, true);
                    $history[time()] = $template->template;
                    if (count($history) > $historyLimit) {
                        $history = array_slice($history, 1, $historyLimit, true);
                    }
                    $templateHistory = json_encode($history);
                } else {
                    $templateHistory = json_encode([time() => $template->template]);
                }
            } else {
                $templateHistory = $template->history;
            }


            $template->parent_id = ((isset($fields['template_parent_id']) && ($fields['template_parent_id'] != '----')) ?
                (int)$fields['template_parent_id'] : null);
            $template->name      = $fields['name'];
            $template->device    = ((isset($fields['device']) && ($fields['device'] != '----')) ? $fields['device'] : null);
            $template->template  = (isset($fields['template_source']) ? $fields['template_source'] : null);
            $template->history   = $templateHistory;
            $template->visible   = (int)$fields['visible'];
            $template->save();

            $this->data = array_merge($this->data, $template->getColumns());
        }
    }

    /**
     * Copy template
     *
     * @param  boolean $fields
     * @return void
     */
    public function copy($fields = false)
    {
        $oldId    = (int)$this->data['id'];
        $template = Table\Templates::findById($oldId);

        if (isset($template->id)) {
            $i    = 1;
            $name = $template->name . ' (Copy ' . $i . ')';

            $dupeTemplate = Table\Templates::findBy(['name' => $name]);

            while (isset($dupeTemplate->id)) {
                $i++;
                $name = $template->name . ' (Copy ' . $i . ')';
                $dupeTemplate = Table\Templates::findBy(['name' => $name]);
            }

            $newTemplate = new Table\Templates([
                'parent_id' => $template->parent_id,
                'name'      => $name,
                'device'    => (null !== $template->parent_id) ? null : 'desktop',
                'template'  => $template->template,
                'history'   => $template->history
            ]);
            $newTemplate->save();

            if ($fields) {
                $fv = \Phire\Fields\Table\FieldValues::findBy(['model_id' => $oldId]);
                if ($fv->count() > 0) {
                    foreach ($fv->rows() as $value) {
                        $v = new \Phire\Fields\Table\FieldValues([
                            'field_id'  => $value->field_id,
                            'model_id'  => $newTemplate->id,
                            'model'     => 'Phire\Templates\Model\Template',
                            'value'     => $value->value,
                            'timestamp' => time(),
                            'history'   => $value->history
                        ]);
                        $v->save();
                    }
                }
            }

            $this->data = array_replace($this->data, $newTemplate->getColumns());
        }
    }

    /**
     * Remove a template
     *
     * @param  array $fields
     * @return void
     */
    public function remove(array $fields)
    {
        if (isset($fields['rm_templates'])) {
            foreach ($fields['rm_templates'] as $id) {
                $template = Table\Templates::findById((int)$id);
                if (isset($template->id)) {
                    $template->delete();
                }
            }
        }
    }

    /**
     * Determine if list of templates has pages
     *
     * @param  int $limit
     * @return boolean
     */
    public function hasPages($limit)
    {
        return (Table\Templates::findAll()->count() > $limit);
    }

    /**
     * Get count of templates
     *
     * @return int
     */
    public function getCount()
    {
        return Table\Templates::findAll()->count();
    }

}
