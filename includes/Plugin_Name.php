<?php

namespace Plugin_Name\Includes;

use InvalidArgumentException;

use const Plugin_Name\MAIN_PATH;

/**
 * The main plugin class
 *
 * @since      1.0.0
 * @package    pl
 */
class Plugin_Name
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
     * Main plugin path /wp-content/plugins/<plugin-folder>/.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $pluginPath Main path.
     */
    private static $pluginPath = '';

    /**
     * Plugin Template path /wp-content/plugins/<plugin-folder>/app/Templates/.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $pluginTemplatePath Main path.
     */
    private static $pluginTemplatePath = '';

    /**
     * Plugin Template path /wp-content/plugins/<plugin-folder>/app/Templates/.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $pluginTemplateRelativePath Main path.
     */
    private static $pluginTemplateRelativePath = '';

    /**
     * Absolute plugin url <wordpress-root-folder>/wp-content/plugins/<plugin-folder>/.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $pluginUrl Main path.
     */
    private static $pluginUrl = '';

    /**
     * Version number of plugin
     *
     * @var null|string
     */
    private static $version = null;

    /**
     * Wordpres options cache
     *
     * @var array
     */
    private static $options = [];

    /**
     * @var mixed
     */
    private $router;

    /**
     * Define the core functionality of the plugin.
     *
     * Load the dependencies, define the locale, and bootstraps Router.
     *
     * @param mixed $routerClassName Name of the Router class to load. Otherwise false.
     * @param mixed $routes          File that contains list of all routes. Otherwise false.
     *
     * @since    1.0.0
     */
    public function __construct($routerClassName = false, $routes = false, $bootstrap = false)
    {
        $this->routerClassName = $routerClassName;
        $this->routes = $routes;
        $this->boostrap = $bootstrap;
    }

    /**
     * Define the core functionality of the plugin.
     *
     * Load the dependencies, define the locale, and bootstraps Router.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->setLocale();

        if ($this->routerClassName !== false and $this->routes !== false) {
            $this->initRouter($this->routerClassName, $this->routes, $this->boostrap);
        }
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0.0
     */
    private function setLocale(): void
    {
        $plugin_i18n = new I18n();
        $plugin_i18n->setDomain(self::PLUGIN_ID);

        add_action('plugins_loaded', [$plugin_i18n, 'loadPluginTextdomain']);
    }

    /**
     * Init Router
     *
     * @param mixed $routerClassName Name of the Router class to load.
     * @param mixed $routes          File that contains list of all routes.
     *
     * @return void
     * @throws InvalidArgumentException If Router class or Routes file is not found.
     * @since 1.0.0
     */
    private function initRouter($routerClassName, $routes, $bootstrap)
    {
        if (!class_exists($routerClassName)) {
            throw new InvalidArgumentException("Could not load {$routerClassName} class!");
        }

        if (!file_exists($routes)) {
            throw new InvalidArgumentException("Routes file {$routes} not found! Please pass a valid file.");
        }

        $this->router = $router = new $routerClassName($bootstrap); // @codingStandardsIgnoreLine.
        add_action(
            'plugins_loaded',
            function () use ($router, $routes) {
                include_once($routes);
            }
        );
    }

    /**
     * Get plugin's absolute path.
     *
     * @since    1.0.0
     */
    public static function getPluginPath(): string
    {
        if ('' === self::$pluginPath) {
            self::$pluginPath = MAIN_PATH;
        }

        return self::$pluginPath;
    }

    /**
     * Get plugin's dir name.
     */
    public static function getPluginDirName(): string
    {
        $dir = trim(self::getPluginPath(), DIRECTORY_SEPARATOR);
        $dirs = explode(DIRECTORY_SEPARATOR, $dir);

        return end($dirs);
    }

    /**
     * Get plugin's Templates directory absolute path.
     *
     * @since    1.0.0
     */
    public static function getPluginTemplatesPath(): string
    {
        if (self::$pluginTemplatePath === '') {
            self::$pluginTemplatePath = self::getPluginPath(
                ) . 'app' . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR;
        }

        return self::$pluginTemplatePath;
    }

    /**
     * Get plugin's Templates directory relative path.
     */
    public static function getPluginTemplatesRelativePath(): string
    {
        if (self::$pluginTemplateRelativePath === '') {
            self::$pluginTemplateRelativePath = DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR;
        }

        return self::$pluginTemplateRelativePath;
    }

    /**
     * Get plugin's absolute url.
     *
     * @since    1.0.0
     */
    public static function getPluginUrl(): string
    {
        if (self::$pluginUrl === '') {
            self::$pluginUrl = plugin_dir_url(dirname(__FILE__, 2));
        }

        return self::$pluginUrl;
    }

    public static function getPluginVersion(): string
    {
        if (self::$version === null) {
            $plugin_data = get_plugin_data(MAIN_PATH . "plugin-name.php");
            self::$version = $plugin_data['Version'];
        }

        return self::$version;
    }

    public static function getOptions(): array
    {
        if (self::$options === []) {
            $options = get_option('plugin-name', false);

            if (is_array($options)) {
                self::$options = get_option('plugin-name', false);
            }
        }

        return self::$options;
    }
}
