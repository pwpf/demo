<?php

namespace Plugin_Name\App\Models\Admin;

use Plugin_Name\App\Models\Settings as Settings_Model;

/**
 * Model class that implements Plugin Admin Settings
 */
class Admin_Settings extends AbstractAdminModel
{

    /**
     * Constructor
     *
     * @since    1.0.0
     */
    protected function __construct()
    {
        $this->registerHookCallbacks();
    }

    /**
     * Register callbacks for actions and filters
     *
     * @since    1.0.0
     */
    protected function registerHookCallbacks()
    {
        /**
         * If you think all model related add_actions & filters should be in
         * the model class only, then this this the place where you can place
         * them.
         *
         * You can remove this method if you are not going to use it.
         */
    }

    /**
     * Register settings
     *
     * @since    1.0.0
     */
    public function registerSettings()
    {
        // The settings container.
        register_setting(
            Settings_Model::SETTINGS_NAME,     // Option group Name.
            Settings_Model::SETTINGS_NAME,     // Option Name.
            [$this, 'sanitize'] // Sanitize.
        );
    }

    /**
     * Validates submitted setting values before they get saved to the database.
     *
     * @param array $input Settings Being Saved.
     *
     * @return array
     * @since    1.0.0
     */
    public function sanitize($input)
    {
        $new_input = [];
        if (isset($input) && !empty($input)) {
            $new_input = $input;
        }

        return $new_input;
    }

    /**
     * Returns the option key used to store the settings in database
     *
     * @return string
     * @since 1.0.0
     */
    public function getPluginSettingsOptionKey()
    {
        return Settings_Model::getPluginSettingsOptionKey();
    }

    /**
     * Retrieves all of the settings from the database
     *
     * @param string $setting_name Setting to be retrieved.
     *
     * @return array
     * @since    1.0.0
     */
    public function getSetting($setting_name)
    {
        return Settings_Model::getSetting($setting_name);
    }

}
