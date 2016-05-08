<?php

namespace Phire\Templates\Model;

use Phire\Templates\Table;
use Phire\Model\AbstractModel;
use Pop\Archive\Archive;
use Pop\File\Dir;
use Pop\File\Upload;

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
     * Upload template
     *
     * @param  array $file
     * @return void
     */
    public function upload($file)
    {
        $templatePath = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/templates';
        if (!file_exists($templatePath)) {
            mkdir($templatePath);
            chmod($templatePath, 0777);
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/index.html')) {
                copy(
                    $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/index.html',
                    $templatePath . '/index.html'
                );
                chmod($templatePath . '/index.html', 0777);
            }
        }

        $upload   = new Upload($templatePath);
        $template = $upload->upload($file);

        $formats = Archive::getFormats();

        if (file_exists($templatePath . '/' . $template)) {
            $ext  = null;
            $name = null;
            if (substr($template, -4) == '.zip') {
                $ext  = 'zip';
                $name = substr($template, 0, -4);
            } else if (substr($template, -4) == '.tgz') {
                $ext  = 'tgz';
                $name = substr($template, 0, -4);
            } else if (substr($template, -7) == '.tar.gz') {
                $ext  = 'tar.gz';
                $name = substr($template, 0, -7);
            }

            if ((null !== $ext) && (null !== $name) && array_key_exists($ext, $formats)) {
                $archive = new Archive($templatePath . '/' . $template);
                $archive->extract($templatePath);
                if ((stripos($template, 'gz') !== false) && (file_exists($templatePath . '/' . $name . '.tar'))) {
                    unlink($templatePath . '/' . $name . '.tar');
                }

                if (file_exists($templatePath . '/' . $name)) {
                    $dir = new Dir($templatePath . '/' . $name, ['filesOnly' => true]);
                    foreach ($dir->getFiles() as $file) {
                        if (substr($file, -5) == '.html') {
                            $isVisible = ((stripos($file, 'category') === false) &&
                                (stripos($file, 'error') === false) &&
                                (stripos($file, 'tag') === false) &&
                                (stripos($file, 'search') === false) &&
                                (stripos($file, 'header') === false) &&
                                (stripos($file, 'footer') === false)
                            );
                            $template  = new Table\Templates([
                                'parent_id' => null,
                                'name'      => substr($file, 0, -5),
                                'device'    => 'desktop',
                                'template'  => file_get_contents($templatePath . '/' . $name . '/' . $file),
                                'history'   => null,
                                'visible'   => (int)$isVisible
                            ]);
                            $template->save();
                        }
                    }
                }
            }
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
     * @param  \Pop\Module\Manager $modules
     * @return void
     */
    public function copy(\Pop\Module\Manager $modules)
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

            if ($modules->isRegistered('phire-fields')) {
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
