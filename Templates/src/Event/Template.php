<?php

namespace Templates\Event;

use Templates\Table;
use Pop\Application;
use Phire\Controller\AbstractController;

class Template
{

    /**
     * Bootstrap the module
     *
     * @param  Application $application
     * @return void
     */
    public static function bootstrap(Application $application)
    {
        if ($application->isRegistered('Content')) {
            $templates = Table\Templates::findBy(['parent_id' => null]);
            if ($templates->hasRows()) {
                $forms  = $application->config()['forms'];
                foreach ($templates->rows() as $template) {
                    if (self::checkTemplateName($template->name)) {
                        $forms['Content\Form\Content'][0]['content_template']['value'][$template->id] = $template->name;
                    }
                }
                $application->mergeConfig(['forms' => $forms], true);
            }
        }
    }

    /**
     * Set the template for the content
     *
     * @param  AbstractController $controller
     * @param  Application        $application
     * @return void
     */
    public static function setTemplate(AbstractController $controller, Application $application)
    {
        if ($application->isRegistered('Content') &&
            ($controller instanceof \Content\Controller\IndexController) && ($controller->hasView())) {
            if (is_numeric($controller->getTemplate())) {
                $template = ($controller->getTemplate() == -1) ? Table\Templates::findBy(['name' => 'Error']) :
                    Table\Templates::findById((int)$controller->getTemplate());
                if (isset($template->id)) {
                    $controller->view()->setTemplate($template->template);
                }
            }
        }
    }

    /**
     * Check if the template is allowed
     *
     * @param  string $name
     * @return boolean
     */
    public static function checkTemplateName($name)
    {
        $templates = [
            'error', 'category', 'header', 'footer', 'sidebar'
        ];

        return (!in_array(strtolower($name), $templates));
    }

}
