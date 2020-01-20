<?php

namespace Plugin_Name\App\Controller\Admin;

use Plugin_Name\Includes\Plugin_Name;

/**
 * Controller class that implements Plugin Admin Settings configurations
 */
class AdminSettings extends AbstractAdminController
{

    /**
     * Slug of the Settings Page
     *
     * @since    1.0.0
     */
    public const SETTINGS_PAGE_SLUG = Plugin_Name::PLUGIN_ID;

    /**
     * Capability required to access settings page
     *
     * @since 1.0.0
     */
    public const REQUIRED_CAPABILITY = 'manage_options';

    /**
     * Holds suffix for dynamic add_action called on settings page.
     *
     * @var string
     * @since 1.0.0
     */
    private static $hookSuffix = 'settings_page_' . Plugin_Name::PLUGIN_ID;

    /**
     * Register callbacks for actions and filters
     *
     * @since    1.0.0
     */
    public function registerHookCallbacks()
    {
        // Create Menu.
        add_action('admin_menu', [$this, 'pluginMenu']);

        // Enqueue Styles & Scripts.
        add_action('admin_print_scripts-' . static::$hookSuffix, [$this, 'enqueueScripts']);
        add_action('admin_print_styles-' . static::$hookSuffix, [$this, 'enqueueStyles']);

        // Register Fields.
        add_action('load-' . static::$hookSuffix, [$this, 'register_fields']);

        /** @var \Plugin_Name\App\Model\Admin\Admin_Settings $AdminSettingsModel */
        $AdminSettingsModel = $this->loadModel('Admin/Admin_Settings', '\Plugin_Name\App');

        // Register Settings.
        add_action('admin_init', [$AdminSettingsModel, 'registerSettings']);

        // Settings Link on Plugin's Page.
        add_filter(
            'plugin_action_links_' . Plugin_Name::PLUGIN_ID . '/' . Plugin_Name::PLUGIN_ID . '.php',
            [$this, 'add_plugin_action_links']
        );
    }

    /**
     * Create menu for Plugin inside Settings menu
     *
     * @since    1.0.0
     */
    public function pluginMenu()
    {
        static::$hookSuffix = add_options_page(
            __('Plugin_name', Plugin_Name::PLUGIN_ID),        // Page Title.
            __('Plugin name settings', Plugin_Name::PLUGIN_ID),        // Menu Title.
            static::REQUIRED_CAPABILITY,           // Capability.
            static::SETTINGS_PAGE_SLUG,             // Menu URL.
            [$this, 'markup_settings_page'] // Callback.
        );
        // add_menu_page(
        //     'Main',
        //     __( 'Plugin_Name', Plugin_Name::PLUGIN_ID ),
        //     static::REQUIRED_CAPABILITY,
        //     static::SETTINGS_PAGE_SLUG,             // Menu URL.
        //     array( $this, 'markup_settings_page') // Callback.
        // );
        // @codingStandardsIgnoreEnd.
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueueScripts()
    {
        /**
         * This function is provided for demonstration purposes only.
         */

        wp_enqueue_script(
            Plugin_Name::PLUGIN_ID . '_admin-js',
            Plugin_Name::getPluginUrl() . 'assets/js/admin/app.js',
            ['jquery'],
            Plugin_Name::PLUGIN_VERSION,
            true
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueueStyles()
    {
        /**
         * This function is provided for demonstration purposes only.
         */
        wp_enqueue_style(
            Plugin_Name::PLUGIN_ID . '_admin-css',
            Plugin_Name::getPluginUrl() . 'assets/css/admin/style.css',
            [],
            Plugin_Name::PLUGIN_VERSION,
            'all'
        );
    }

    /**
     * Creates the markup for the Settings page
     *
     * @since    1.0.0
     */
    public function markup_settings_page()
    {
        if (current_user_can(static::REQUIRED_CAPABILITY)) {
            return print $this->view->render(
                'admin/page-settings/page-settings.php',
                [
                    'page_title' => Plugin_Name::PLUGIN_NAME,
                    'settings_name' => $this->getModel()->getPluginSettingsOptionKey(),
                ]
            );
        } else {
            wp_die(__('Access denied.')); // WPCS: XSS OK.
        }
    }

    /**
     * Registers settings sections and fields
     *
     * @since    1.0.0
     */
    public function register_fields()
    {
        // Add Settings Page Section.
        add_settings_section(
            'plugin_name_main_settings',                    // Section ID.
            __('Plugin Config', Plugin_Name::PLUGIN_ID), // Section Title.
            [$this, 'markup_section_headers'], // Section Callback.
            static::SETTINGS_PAGE_SLUG                 // Page URL.
        );
        add_settings_section(
            'plugin_name_other_settings',                    // Section ID.
            __('Plugin Name Other settings', Plugin_Name::PLUGIN_ID), // Section Title.
            [$this, 'markup_section_headers'], // Section Callback.
            static::SETTINGS_PAGE_SLUG                 // Page URL.
        );
    }


    /**
     * Adds the section introduction text to the Settings page
     *
     * @param array $section Array containing information Section Id, Section
     *                       Title & Section Callback.
     *
     * @since    1.0.0
     */
    public function markup_section_headers($section)
    {
        return print $this->view->render(
            'admin/page-settings/page-settings-section-headers.php',
            [
                'section' => $section,
                'text_example' => __('Please configure the plugin', Plugin_Name::PLUGIN_ID),
            ]
        );
    }

    /**
     * Delivers the markup for settings fields
     *
     * @param array $field_args Field arguments passed in `add_settings_field`
     *                          function.
     *
     * @since    1.0.0
     */
    public function markup_fields($field_args)
    {
        $field_id = $field_args['id'];
        $settings_value = $this->getModel()->getSetting($field_id);
        return print $this->view->render(
            'admin/page-settings/page-settings-fields.php',
            [
                'field_id' => esc_attr($field_id),
                'settings_name' => $this->getModel()->getPluginSettingsOptionKey(),
                'settings_value' => !empty($settings_value) ? esc_attr($settings_value) : '',
            ]
        );
    }

    /**
     * Adds links to the plugin's action link section on the Plugins page
     *
     * @param array $links The links currently mapped to the plugin.
     *
     * @return array
     *
     * @since    1.0.0
     */
    public function add_plugin_action_links($links)
    {
        $settings_link = '<a href="options-general.php?page=' . static::SETTINGS_PAGE_SLUG . '">' . __(
                'Settings',
                Plugin_Name::PLUGIN_ID
            ) . '</a>';
        array_unshift($links, $settings_link);

        return $links;
    }

}
