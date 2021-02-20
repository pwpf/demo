<?php

namespace Plugin_Name\App\Model;

use Plugin_Name\Includes\Plugin_Name;

/**
 * Implements operations related to Plugin Settings.
 *
 * @since      1.0.0
 */
class Settings extends AbstractModel
{
    public const SETTINGS_NAME = Plugin_Name::PLUGIN_ID;
    /**
     * Holds all Settings
     *
     * @var array
     * @since 1.0.0
     */
    protected static $settings;

    /**
     * Returns the Option name/key saved in the database
     *
     * @return string
     * @since 1.0.0
     */
    public static function getPluginSettingsOptionKey()
    {
        return Plugin_Name::PLUGIN_ID;
    }

    /**
     * Helper method that retuns all Saved Settings related to Plugin
     *
     * @return array
     * @since 1.0.0
     */
    public static function getSettings()
    {
        if (!isset(static::$settings)) {
            static::$settings = get_option(Plugin_Name::PLUGIN_ID, []);
        }

        return static::$settings;
    }

    /**
     * Helper method that returns a individual setting
     *
     * @param string $settingName Setting to be retrieved.
     *
     * @return mixed
     * @since 1.0.0
     */
    public static function getSetting($settingName)
    {
        $allSettings = static::getSettings();

        return isset($allSettings[$settingName]) ? $allSettings[$settingName] : [];
    }

    /**
     * Helper method to delete all settings related to plugin
     *
     * @return void
     * @since 1.0.0
     */
    public static function deleteSettings()
    {
        static::$settings = [];
        delete_option(Plugin_Name::PLUGIN_ID);
    }

    /**
     * Helper method to delete a specific setting
     *
     * @param string $settingName Setting to be Deleted.
     *
     * @return void
     * @since 1.0.0
     */
    public static function deleteSetting($settingName)
    {
        $allSettings = static::getSettings();

        if (isset($allSettings[$settingName])) {
            unset($allSettings[$settingName]);
            static::$settings = $allSettings;
            update_option(Plugin_Name::PLUGIN_ID, $allSettings);
        }
    }

    /**
     * Helper method to Update Settings
     *
     * @param array $new_settings New Setting Values to store.
     *
     * @return void
     * @since 1.0.0
     */
    public static function updateSettings($new_settings)
    {
        $allSettings = static::getSettings();
        $updated_settings = array_merge($allSettings, $new_settings);
        static::$settings = $updated_settings;
        update_option(Plugin_Name::PLUGIN_ID, $updated_settings);
    }

    /**
     * Helper method Update Single Setting
     *
     * Similar to updateSettings, this function won't by called anywhere automatically.
     * This is a custom helper function to delete individual setting. You can
     * delete this method if you don't want this ability.
     *
     * @param string $settingName  Setting to be Updated.
     * @param mixed  $settingValue New value to set for that setting.
     *
     * @return void
     * @since 1.0.0
     */
    public static function updateSetting($settingName, $settingValue)
    {
        static::updateSetting([$settingName => $settingValue]);
    }
}
