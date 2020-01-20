<?php

namespace Plugin_Name\Includes;

use InvalidArgumentException;
use Plugin_NameVendor\PWPF\Registry\ControllerRegistry;
use Plugin_NameVendor\PWPF\Registry\ModelRegistry;


/**
 * The main plugin class
 */
class Plugin_Name extends DependencyLoader
{

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     */
    public const PLUGIN_ID = 'plugin-name';

    /**
     * The name identifier of this plugin.
     *
     * @since    1.0.0
     */
    public const PLUGIN_NAME = 'Plugin Name';

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     */
    public const PLUGIN_VERSION = '1.0.0';

    /**
     * Holds instance of this class
     *
     * @since    1.0.0
     * @access   private
     * @var      Plugin_Name $instance Instance of this class.
     */
    private static $instance;

    /**
     * Main plugin path /wp-content/plugins/<plugin-folder>/.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $pluginPath Main path.
     */
    private static $pluginPath;

    /**
     * Absolute plugin url <wordpress-root-folder>/wp-content/plugins/<plugin-folder>/.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $pluginUrl Main path.
     */
    private static $pluginUrl;

    private $params;

    /**
     * Define the core functionality of the plugin.
     *
     * Load the dependencies, define the locale, and bootstraps Router.
     *
     * @param mixed $routerClassName Name of the Router class to load. Otherwise false.
     * @param mixed $routes            File that contains list of all routes. Otherwise false.
     *
     * @since    1.0.0
     */
    public function __construct($routerClassName = false, $routes = false)
    {
        self::$pluginPath = plugin_dir_path(dirname(__FILE__));
        self::$pluginUrl = plugin_dir_url(dirname(__FILE__));

        $this->setLocale();

        if (false !== $routerClassName && false !== $routes) {
            $this->initRouter($routerClassName, $routes);
        }

        $this->controllers = $this->getAllControllers();
        $this->models = $this->getAllModels();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0.0
     */
    private function setLocale()
    {
        $plugin_i18n = new I18n();
        $plugin_i18n->setDomain(Plugin_Name::PLUGIN_ID);

        add_action('plugins_loaded', [$plugin_i18n, 'loadPluginTextdomain']);
    }

    /**
     * Init Router
     *
     * @param mixed $routerClassName Name of the Router class to load.
     * @param mixed $routes            File that contains list of all routes.
     *
     * @return void
     * @throws InvalidArgumentException If Router class or Routes file is not found.
     * @since 1.0.0
     */
    private function initRouter($routerClassName, $routes)
    {
        if (!class_exists($routerClassName)) {
            throw new InvalidArgumentException("Could not load {$routerClassName} class!");
        }

        if (!file_exists($routes)) {
            throw new InvalidArgumentException("Routes file {$routes} not found! Please pass a valid file.");
        }

        $this->router = $router = new $routerClassName(); // @codingStandardsIgnoreLine.
        add_action(
            'plugins_loaded',
            function () use ($router, $routes) {
                include_once($routes);
            }
        );
    }

    /**
     * Returns all controller objects used for current requests
     *
     * @return object
     * @since    1.0.0
     */
    private function getAllControllers()
    {
        return (object)ControllerRegistry::getAllObjects();
    }

    /**
     * Returns all model objecs used for current requests
     *
     * @return object
     * @since   1.0.0
     */
    private function getAllModels()
    {
        return (object)ModelRegistry::getAllObjects();
    }

    /**
     * Get plugin's absolute path.
     *
     * @since    1.0.0
     */
    public static function getPluginPath()
    {
        return isset(self::$pluginPath) ? self::$pluginPath : plugin_dir_path(dirname(__FILE__));
    }

    /**
     * Get plugin's absolute url.
     *
     * @since    1.0.0
     */
    public static function getPluginUrl()
    {
        return isset(self::$pluginUrl) ? self::$pluginUrl : plugin_dir_url(dirname(__FILE__));
    }

    /**
     * Method that retuns all Saved Settings related to Plugin.
     *
     * Only to be used by third party developers.
     *
     * @return array
     * @since 1.0.0
     */
    public static function getSettings()
    {
        return Settings::getSettings();
    }

    /**
     * Method that returns a individual setting
     *
     * Only to be used by third party developers.
     *
     * @param string $setting_name Setting to be retrieved.
     *
     * @return mixed
     * @since 1.0.0
     */
    public static function getSetting($setting_name)
    {
        return Settings::getSetting($setting_name);
    }

}
