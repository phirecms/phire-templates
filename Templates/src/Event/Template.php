<?php

namespace Templates\Event;

use Templates\Table;
use Pop\Application;
use Pop\Web\Mobile;
use Pop\Web\Session;
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
        $template = null;

        if ($application->isRegistered('Categories') &&
            ($controller instanceof \Categories\Controller\IndexController) && ($controller->hasView())) {
            $template = Table\Templates::findBy(['name' => 'Category']);
            if (isset($template->id)) {

            }
        } else if ($application->isRegistered('Content') &&
            ($controller instanceof \Content\Controller\IndexController) && ($controller->hasView())) {
            if (is_numeric($controller->getTemplate())) {
                if ($controller->getTemplate() == -1) {
                    $template = Table\Templates::findBy(['name' => 'Error']);
                } else if ($controller->getTemplate() == -2) {
                    $template = Table\Templates::findBy(['name' => 'Date']);
                } else {
                    $template = Table\Templates::findById((int)$controller->getTemplate());
                }
            }
        }

        if ((null !== $template) && isset($template->id)) {
            if (isset($template->id)) {
                $device = self::getDevice($controller->request()->getQuery('mobile'));
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
                $controller->view()->setTemplate(self::parse($tmpl));
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
            'date', 'category', 'error', 'footer', 'header', 'sidebar'
        ];

        return (!in_array(strtolower($name), $templates));
    }

    /**
     * Parse the template
     *
     * @param  string $template
     * @param  mixed  $id
     * @param  mixed  $pid
     * @return boolean
     */
    public static function parse($template, $id = null, $pid = null)
    {
        // Parse any date placeholders
        $dates = [];
        preg_match_all('/\[\{date.*\}\]/', $template, $dates);
        if (isset($dates[0]) && isset($dates[0][0])) {
            foreach ($dates[0] as $date) {
                $pattern  = str_replace('}]', '', substr($date, (strpos($date, '_') + 1)));
                $template = str_replace($date, date($pattern), $template);
            }
        }

        // Parse any template placeholders
        $templates = [];
        preg_match_all('/\[\{template_.*\}\]/', $template, $templates);
        if (isset($templates[0]) && isset($templates[0][0])) {
            foreach ($templates[0] as $tmpl) {
                $t = str_replace('}]', '', substr($tmpl, (strpos($tmpl, '_') + 1)));
                if (($t != $id) && ($t != $pid)) {
                    $newTemplate = (is_numeric($t)) ? Table\Templates::findById($t) : Table\Templates::findBy(['name' => $t]);
                    if (isset($newTemplate->id)) {
                        $t = self::parse($newTemplate->template, $newTemplate->id, $id);
                        $template = str_replace($tmpl, $t, $template);
                    } else {
                        $template = str_replace($tmpl, '', $template);
                    }
                } else {
                    $template = str_replace($tmpl, '', $template);
                }
            }
        }

        // Parse any session placeholders
        $open  = [];
        $close = [];
        $merge = [];
        $sess  = [];
        preg_match_all('/\[\{sess\}\]/msi', $template, $open, PREG_OFFSET_CAPTURE);
        preg_match_all('/\[\{\/sess\}\]/msi', $template, $close, PREG_OFFSET_CAPTURE);

        // If matches are found, format and merge the results.
        if ((isset($open[0][0])) && (isset($close[0][0]))) {
            foreach ($open[0] as $key => $value) {
                $merge[] = [$open[0][$key][0] => $open[0][$key][1], $close[0][$key][0] => $close[0][$key][1]];
            }
        }
        foreach ($merge as $match) {
            $sess[] = substr($template, $match['[{sess}]'], (($match['[{/sess}]'] - $match['[{sess}]']) + 9));
        }

        if (count($sess) > 0) {
            $session = Session::getInstance();
            foreach ($sess as $s) {
                $sessString = str_replace(['[{sess}]', '[{/sess}]'], ['', ''], $s);
                $isSess = null;
                $noSess = null;
                if (strpos($sessString, '[{or}]') !== false) {
                    $sessValues = explode('[{or}]', $sessString);
                    if (isset($sessValues[0])) {
                        $isSess = $sessValues[0];
                    }
                    if (isset($sessValues[1])) {
                        $noSess = $sessValues[1];
                    }
                } else {
                    $isSess = $sessString;
                }
                if (null !== $isSess) {
                    if (!isset($session->user)) {
                        $template = str_replace($s, $noSess, $template);
                    } else {
                        $newSess = $isSess;
                        foreach ($_SESSION as $sessKey => $sessValue) {
                            if ((is_array($sessValue) || ($sessValue instanceof \ArrayObject)) &&
                                (strpos($template, '[{' . $sessKey . '->') !== false)) {
                                foreach ($sessValue as $sessK => $sessV) {
                                    if (!is_array($sessV)) {
                                        $newSess = str_replace('[{' . $sessKey . '->' . $sessK . '}]', $sessV, $newSess);
                                    }
                                }
                            } else if (!is_array($sessValue) && !($sessValue instanceof \ArrayObject) &&
                                (strpos($template, '[{' . $sessKey) !== false)) {
                                $newSess = str_replace('[{' . $sessKey . '}]', $sessValue, $newSess);
                            }
                        }
                        if ($newSess != $isSess) {
                            $template = str_replace('[{sess}]' . $sessString . '[{/sess}]', $newSess, $template);
                        } else {
                            $template = str_replace($s, $noSess, $template);
                        }
                    }
                } else {
                    $template = str_replace($s, '', $template);
                }
            }
        }

        return $template;
    }

    /**
     * Method to determine the mobile device
     *
     * @param  string $mobile
     * @return string
     */
    protected static function getDevice($mobile = null)
    {
        $session = Session::getInstance();

        if (null !== $mobile) {
            $force = $mobile;
            if ($force == 'clear') {
                unset($session->mobile);
            } else {
                $session->mobile = $force;
            }
        }

        if (!isset($session->mobile)) {
            $device = Mobile::getDevice();
            if (null !== $device) {
                $device = strtolower($device);
                if (($device == 'android') || ($device == 'windows')) {
                    $device .= (Mobile::isTabletDevice()) ? '-tablet' : '-phone';
                }
            }
        } else {
            $device = $session->mobile;
        }

        return $device;
    }

}
