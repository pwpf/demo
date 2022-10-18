<?php
/**
 * Main Plugin File
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Plugin_Name
 *
 * @wordpress-plugin
 * Plugin Name:       PWPF - Plugin WordPress Framework
 * Plugin URI:        http://example.com/plugin-name-uri/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Your Name or Your Company
 * Author URI:        http://example.com/
 * Text Domain:       plugin-name
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
use Plugin_Name\App\Activator;
use Plugin_Name\App\Deactivate;
use Plugin_Name\App\Update;
use Plugin_Name\Includes\RequirementsChecker;

use const Plugin_Name\MAIN_PATH;

if (!defined('WPINC')) {
    die;
}

define('Plugin_Name\MAIN_PATH', __DIR__ . DIRECTORY_SEPARATOR);

/**
 * Load PSR-4
 */
require_once __DIR__ . '/vendor_prefixed/autoload.php';
include_once __DIR__ . '/vendor_prefixed/dframe/src/Functions.php';


/**
 * Creates/Maintains the object of Requirements Checker Class
 *
 * @return RequirementsChecker
 * @since 1.0.0
 */
try {
    register_activation_hook(MAIN_PATH . 'plugin-name.php', [new Activator(), 'activate']);
    register_deactivation_hook(MAIN_PATH . 'plugin-name.php', [new Deactivate(), 'deactivate']);
    add_action('upgrader_process_complete', [new Update(), 'update'], 10, 2);

    $requirementsChecker = \Plugin_Name\Includes\RequirementsChecker::getPluginRequirementsChecker();
    if (!$requirementsChecker->requirementsMet()) {
        add_action('admin_notices', [$requirementsChecker, 'showRequirementsErrors']);

        // Deactivate plugin immediately if requirements are not met.
        require_once(ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'plugin.php');
        deactivate_plugins(plugin_basename(__FILE__));

        throw new Exception('Plugin Name plugin requirements are not met.');
    }
} catch (Exception $e) {
    return (new \Plugin_Name\App\Deactivate())->deactivate();
}


if (!function_exists('dieTriggerError')) {
    function dieTriggerError($e)
    {
        add_action('get_header', function () use ($e) {
            if (is_user_logged_in()) {
                wp_die($e);
            }

            wp_die(
                '<h1>There is a problem with website.</h1><br />We got the information and are already on it. Please check back later.'
            );
        });
    }
}


try {
    $app = new \Plugin_Name\Includes\Plugin_Name::getPluginName();
} catch (Throwable $e) {
    dieTriggerError($e);
}
