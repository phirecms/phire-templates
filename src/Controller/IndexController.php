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
namespace Phire\Templates\Controller;

use Phire\Templates\Model;
use Phire\Templates\Form;
use Phire\Templates\Table;
use Phire\Controller\AbstractController;

/**
 * Templates Index Controller class
 *
 * @category   Phire\Templates
 * @package    Phire\Templates
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    1.0.0
 */
class IndexController extends AbstractController
{

    /**
     * Index action method
     *
     * @return void
     */
    public function index()
    {
        $this->prepareView('templates/index.phtml');
        $templates = new Model\Template();

        $this->view->title     = 'Templates';
        $this->view->templates = $templates->getAll($this->request->getQuery('sort'));

        $this->send();
    }

    /**
     * Upload action method
     *
     * @return void
     */
    public function upload()
    {
        if (($_FILES) && !empty($_FILES['upload_template']) && !empty($_FILES['upload_template']['name'])) {
            $template = new Model\Template();
            $template->upload($_FILES['upload_template']);
            $this->sess->setRequestValue('saved', true);
        }

        $this->redirect(BASE_PATH . APP_URI . '/templates');
    }

    /**
     * Add action method
     *
     * @return void
     */
    public function add()
    {
        $this->prepareView('templates/add.phtml');
        $this->view->title = 'Templates : Add';

        $fields = $this->application->config()['forms']['Phire\Templates\Form\Template'];

        $templates = Table\Templates::findAll();
        foreach ($templates->rows() as $tmpl) {
            $fields[0]['template_parent_id']['value'][$tmpl->id] = $tmpl->name;
        }

        $this->view->form = new Form\Template($fields);

        if ($this->request->isPost()) {
            $this->view->form->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($this->request->getPost());

            if ($this->view->form->isValid()) {
                $this->view->form->clearFilters()
                     ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                     ->filter();
                $template = new Model\Template();
                $template->save($this->view->form->getFields());
                $this->view->id = $template->id;
                $this->sess->setRequestValue('saved', true);
                $this->redirect(BASE_PATH . APP_URI . '/templates/edit/'. $template->id);
            }
        }

        $this->send();
    }

    /**
     * Edit action method
     *
     * @param  int $id
     * @return void
     */
    public function edit($id)
    {
        $template = new Model\Template();
        $template->getById($id);

        if (!isset($template->id)) {
            $this->redirect(BASE_PATH . APP_URI . '/templates');
        }

        $this->prepareView('templates/edit.phtml');
        $this->view->title         = 'Templates';
        $this->view->template_name = $template->name;

        $fields = $this->application->config()['forms']['Phire\Templates\Form\Template'];

        $templates = Table\Templates::findAll();
        foreach ($templates->rows() as $tmpl) {
            if ($tmpl->id != $id) {
                $fields[0]['template_parent_id']['value'][$tmpl->id] = $tmpl->name;
            }
        }

        if (null !== $template->history) {
            $history = json_decode($template->history, true);
            //$fields[0]['template_history'] = [
            $tmplHistory = [
                'type'  => 'select',
                'label' => 'Select Revision',
                'value' => [
                    '0' => '(Current)'
                ],
                'attributes' => [
                    'onchange' => 'phire.changeTemplateHistory(this);'
                ]
            ];
            krsort($history);
            foreach ($history as $timestamp => $value) {
                $tmplHistory['value'][$timestamp] = date('M j, Y H:i:s', $timestamp);
            }
            $fields[0] = array_slice($fields[0], 0, 3, true) +
                ['template_history' => $tmplHistory] +
                array_slice($fields[0], 3, count($fields[0]) - 3, true);
        }

        $fields[1]['name']['attributes']['onkeyup'] = 'phire.changeTitle(this.value);';

        $this->view->form = new Form\Template($fields);
        $this->view->form->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
             ->setFieldValues($template->toArray());

        if ($this->request->isPost()) {
            $this->view->form->setFieldValues($this->request->getPost());

            if ($this->view->form->isValid()) {
                $this->view->form->clearFilters()
                     ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                     ->filter();
                $template = new Model\Template();

                $template->update($this->view->form->getFields(), $this->application->module('phire-templates')->config()['history']);
                $this->view->id = $template->id;
                $this->sess->setRequestValue('saved', true);
                $this->redirect(BASE_PATH . APP_URI . '/templates/edit/'. $template->id);
            }
        }

        $this->send();
    }

    /**
     * Copy action method
     *
     * @param  int $id
     * @return void
     */
    public function copy($id)
    {
        $template = new Model\Template();
        $template->getById($id);

        if (!isset($template->id)) {
            $this->redirect(BASE_PATH . APP_URI . '/$templates');
        }

        $template->copy($this->application->modules());
        $this->sess->setRequestValue('saved', true);
        $this->redirect(BASE_PATH . APP_URI . '/templates');
    }

    /**
     * Json action method
     *
     * @param  int $id
     * @param  int $marked
     * @return void
     */
    public function json($id, $marked)
    {
        $json = [];

        $template = Table\Templates::findById($id);
        if (isset($template->id) && (null !== $template->history)) {
            $history = json_decode($template->history, true);
            if (isset($history[$marked])) {
                $json['value'] = $history[$marked];
            }
        }

        $this->response->setBody(json_encode($json, JSON_PRETTY_PRINT));
        $this->send(200, ['Content-Type' => 'application/json']);
    }

    /**
     * Remove action method
     *
     * @return void
     */
    public function remove()
    {
        if ($this->request->isPost()) {
            $template = new Model\Template();
            $template->remove($this->request->getPost());
        }
        $this->sess->setRequestValue('removed', true);
        $this->redirect(BASE_PATH . APP_URI . '/templates');
    }

    /**
     * Prepare view
     *
     * @param  string $template
     * @return void
     */
    protected function prepareView($template)
    {
        $this->viewPath = __DIR__ . '/../../view';
        parent::prepareView($template);
    }

}
