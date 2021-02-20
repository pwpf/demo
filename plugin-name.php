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
use Plugin_Name\Bootstrap;
use Plugin_Name\Includes\Plugin_Name;
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
function plugin_requirements_checker()
{
    static $requirements_checker = null;

    if (null === $requirements_checker) {
        require_once plugin_dir_path(__FILE__) . 'includes/RequirementsChecker.php';
        $requirements_conf = apply_filters(
            'plugin_name_minimum_requirements',
            include_once(plugin_dir_path(__FILE__) . 'app/Config/requirementsConfig.php')
        );
        $requirements_checker = new RequirementsChecker($requirements_conf);
    }

    return $requirements_checker;
}


try {
    /**
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     * @since    1.0.0
     */
    $routerClassName = apply_filters('plugin_name_router_class_name', '\Plugin_NameVendor\PWPF\Routing\Router');
    $routes = apply_filters(
        'plugin_name_routes_file',
        plugin_dir_path(
            __FILE__
        ) . 'app' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'routes.php'
    );


    register_activation_hook(MAIN_PATH . 'plugin-name.php', [new Activator(), 'activate']);
    register_deactivation_hook(MAIN_PATH . 'plugin-name.php', [new Deactivate(), 'deactivate']);
    add_action('upgrader_process_complete', [new Update(), 'update'], 10, 2);

    $app = new Plugin_Name($routerClassName, $routes, Bootstrap::class);
    $app->run();
} catch (\Exception $e) {
    return false;
}