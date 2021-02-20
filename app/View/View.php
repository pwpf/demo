<?php

namespace Plugin_Name\App\View;

/**
 * Class Responsible for Loading Templates
 */
class View extends \Plugin_NameVendor\PWPF\View\View
{
    public function init(array $config = [])
    {
        $config = ['appName' => 'Plugin_Name'];
        parent::init($config);
    }

    public function error($message, $status = 500)
    {
        $backtrace = debug_backtrace();
        $caller = array_shift($backtrace);
        error_log("{$caller['file']}:{$caller['line']} $message");

        if ($status === 404) {
            global $wp_query;

            $wp_query->set_404();
        }

        status_header($status);
        get_template_part($status);

        exit;
    }
}