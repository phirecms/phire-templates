<?php

namespace Templates\Controller;

use Templates\Model;
use Templates\Form;
use Templates\Table;
use Phire\Controller\AbstractController;
use Pop\Paginator\Paginator;

class IndexController extends AbstractController
{

    /**
     * Index action method
     *
     * @param  int $lid
     * @return void
     */
    public function index()
    {
        $this->prepareView('templates/index.phtml');
        $templates = new Model\Template();

        if ($templates->hasPages($this->config->pagination)) {
            $limit = $this->config->pagination;
            $pages = new Paginator($templates->getCount(), $limit);
            $pages->useInput(true);
        } else {
            $limit = null;
            $pages = null;
        }

        $this->view->title     = 'Templates';
        $this->view->pages     = $pages;
        $this->view->templates = $templates->getAll(
            $limit, $this->request->getQuery('page'), $this->request->getQuery('sort')
        );

        $this->send();
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

        $fields = $this->application->config()['forms']['Templates\Form\Template'];
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
                $this->redirect(BASE_PATH . APP_URI . '/templates/edit/'. $template->id . '?saved=' . time());
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

        $fields = $this->application->config()['forms']['Templates\Form\Template'];
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
                $template->update($this->view->form->getFields());
                $this->view->id = $template->id;
                $this->redirect(BASE_PATH . APP_URI . '/templates/edit/'. $template->id . '?saved=' . time());
            }
        }

        $this->send();
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
        $this->redirect(BASE_PATH . APP_URI . '/templates?removed=' . time());
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
