<?php

namespace Templates\Model;

use Templates\Table;
use Phire\Model\AbstractModel;

class Template extends AbstractModel
{

    /**
     * Get all templates
     *
     * @param  int    $limit
     * @param  int    $page
     * @param  string $sort
     * @return array
     */
    public function getAll($limit = null, $page = null, $sort = null)
    {
        $order = (null !== $sort) ? $this->getSortOrder($sort, $page) : 'id DESC';

        if (null !== $limit) {
            $page = ((null !== $page) && ((int)$page > 1)) ?
                ($page * $limit) - $limit : null;

            return Table\Templates::findAll(null, [
                'offset' => $page,
                'limit'  => $limit,
                'order'  => $order
            ])->rows();
        } else {
            return Table\Templates::findAll(null, [
                'order'  => $order
            ])->rows();
        }
    }

    /**
     * Get template by ID
     *
     * @param  int $id
     * @return void
     */
    public function getById($id)
    {
        $template = Table\TEmplates::findById($id);
        if (isset($template->id)) {
            $this->data = array_merge($this->data, $template->getColumns());
        }
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
            'parent_id' => ((isset($fields['template_parent_id'] && ($fields['template_parent_id'] != '----')) ? (int)$fields['template_parent_id'] : null),
            'name'      => $fields['name'],
            'device'    => $fields['device'],
            'template'  => $fields['template_source']
        ]);
        $template->save();

        $this->data = array_merge($this->data, $template->getColumns());
    }

    /**
     * Update an existing template
     *
     * @param  array $file
     * @param  array $fields
     * @return void
     */
    public function update(array $fields)
    {
        $template = Table\Templates::findById((int)$fields['id']);
        if (isset($template->id)) {

            $template->parent_id = ((isset($fields['template_parent_id'] && ($fields['template_parent_id'] != '----')) ? (int)$fields['template_parent_id'] : null);
            $template->name      = $fields['name'];
            $template->device    = $fields['device'];
            $template->template  = $fields['template_source'];
            $template->save();

            $this->data = array_merge($this->data, $template->getColumns());
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
