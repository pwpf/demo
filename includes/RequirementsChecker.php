<?php

namespace Plugin_Name\Includes;


// If this file is called directly, abort.
defined('WPINC') or die;

/**
 * Checks whether plugin's requirements are being met or not
 *
 * @since      1.0.0
 */
class RequirementsChecker
{
    public static $requirementsChecker;

    /**
     * Holds minimum php version for plugin if not defined in `requirements.php`.
     *
     * @var string
     * @since 1.0.0
     */
    private $minPhpVersion = '7.4';

    /**
     * Holds minimum wp version for plugin if not defined in `requirements.php`.
     *
     * @var string
     * @since 1.0.0
     */
    private $minWpVersion = '4.8';

    /**
     * Holds the information whether plugin is compatible with Multisite or not.
     *
     * @var bool
     * @since 1.0.0
     */
    private $isMultisiteCompatible = false;

    /**
     * Holds list of required plugins to be installed and active for our plugin to work
     *
     * @var array
     * @since 1.0.0
     */
    private $requiredPlugins = [];

    /**
     * Holds Error messages if dependencies are not met
     *
     * @var array
     * @since 1.0.0
     */
    private $errors = [];

    /**
     * Constructor
     *
     * @param array $requirementsData Requirements Data mentioned in `requirements.php`.
     *
     * @since 1.0.0
     */
    public function __construct(array $requirementsData = [])
    {
        if (isset($requirementsData['minPhpVersion'])) {
            $this->minPhpVersion = $requirementsData['minPhpVersion'];
        }

        if (isset($requirementsData['minWpVersion'])) {
            $this->minWpVersion = $requirementsData['minWpVersion'];
        }

        if (isset($requirementsData['isMultisiteCompatible'])) {
            $this->isMultisiteCompatible = $requirementsData['isMultisiteCompatible'];
        }

        if (isset($requirementsData['requiredPlugins'])) {
            $this->requiredPlugins = $requirementsData['requiredPlugins'];
        }
    }

    /**
     * Checks if all plugins requirements are met or not
     *
     * @return bool
     * @since 1.0.0
     */
    public function requirementsMet(): bool
    {
        $requirementsMet = true;

        if (!$this->isPhpVersionDependencyMet()) {
            $requirementsMet = false;
        }

        if (!$this->isWpVersionDependencyMet()) {
            $requirementsMet = false;
        }

        if (!$this->isWpMultisiteDependencyMet()) {
            $requirementsMet = false;
        }

        if (!$this->areRequiredPluginsDependencyMet()) {
            $requirementsMet = false;
        }

        return $requirementsMet;
    }

    private static $isRequiredPhpVersionInstalled = null;

    /**
     * Checks if Installed PHP Version is higher than required PHP Version
     *
     * @return bool
     * @since 1.0.0
     */
    private function isPhpVersionDependencyMet(): bool
    {
        if (self::$isRequiredPhpVersionInstalled === null) {
            self::$isRequiredPhpVersionInstalled = version_compare(PHP_VERSION, $this->minPhpVersion, '>=');
        }

        if (self::$isRequiredPhpVersionInstalled === false) {
            $this->addErrorNotice(
                'PHP ' . $this->minPhpVersion . '+ is required',
                'You\'re running version ' . PHP_VERSION
            );
        }

        return self::$isRequiredPhpVersionInstalled;
    }

    /**
     * Adds Error message in $errors variable
     *
     * @param string $errorMessage          Error Message.
     * @param string $supportiveInformation Supportive Information to be displayed along with Error Message in brackets.
     *
     * @return void
     * @since 1.0.0
     */
    private function addErrorNotice(string $errorMessage, string $supportiveInformation): void
    {
        $this->errors[] = (object) [
            'error_message' => $errorMessage,
            'supportive_information' => $supportiveInformation,
        ];
    }

    private static $isRequiredWpVersionInstalled = null;

    /**
     * Checks if Installed WP Version is higher than required WP Version
     *
     * @return bool
     * @since 1.0.0
     */
    private function isWpVersionDependencyMet(): bool
    {
        if (null === self::$isRequiredWpVersionInstalled) {
            global $wp_version;

            self::$isRequiredWpVersionInstalled = version_compare($wp_version, $this->minWpVersion, '>=');
        }

        if (false === self::$isRequiredWpVersionInstalled) {
            $this->addErrorNotice(
                'WordPress ' . $this->minWpVersion . ' or newer is required',
                'You\'re running version ' . $wp_version
            );
        }

        return (bool) self::$isRequiredWpVersionInstalled;
    }

    private static $isWpMultisiteDependencyMet = null;

    /**
     * Checks if Multisite Dependencies are met
     *
     * @return bool
     * @since 1.0.0
     */
    private function isWpMultisiteDependencyMet(): bool
    {
        if (null === self::$isWpMultisiteDependencyMet) {
            self::$isWpMultisiteDependencyMet = !(is_multisite() && (false === $this->isMultisiteCompatible));
        }

        if (false === self::$isWpMultisiteDependencyMet) {
            $this->addErrorNotice(
                'Your site is set up as a Network (Multisite)',
                'This plugin is not compatible with multisite environment'
            );
        }

        return (bool) self::$isWpMultisiteDependencyMet;
    }

    private static $pluginDependencyMet = null;

    /**
     * Checks whether all required plugins are installed & active with proper versions.
     *
     * @return bool
     * @since 1.0.0
     */
    private function areRequiredPluginsDependencyMet(): bool
    {
        if (null === self::$pluginDependencyMet) {
            self::$pluginDependencyMet = true;

            if (empty($this->requiredPlugins)) {
                return true;
            }

            $installedPlugins = array_filter(
                $this->requiredPlugins,
                function ($requiredPluginData, $requiredPluginName) {
                    return $this->isPluginActive($requiredPluginName, $requiredPluginData['pluginSlug']);
                },
                ARRAY_FILTER_USE_BOTH
            );

            // If All Plugins are not installed, set plugin_dependency_met flag as false.
            if (count($installedPlugins) !== count($this->requiredPlugins)) {
                self::$pluginDependencyMet = false;
            }

            $pluginsInstalledWithRequiredVersion = array_filter(
                $installedPlugins,
                function ($requiredPluginData, $requiredPluginName) {
                    return $this->isRequiredPluginVersionActive(
                        $requiredPluginName,
                        $requiredPluginData['pluginSlug'],
                        $requiredPluginData['minPluginVersion']
                    );
                },
                ARRAY_FILTER_USE_BOTH
            );

            // All Plugins did not met minimum version dependency.
            if (count($pluginsInstalledWithRequiredVersion) !== count($this->requiredPlugins)) {
                self::$pluginDependencyMet = false;
            }
        }

        return self::$pluginDependencyMet;
    }

    /**
     * Checks whether plugin is active or not
     *
     * @param string $pluginName Name of the plugin.
     * @param string $pluginSlug Slug of the plugin.
     *
     * @return bool
     * @since 1.0.0
     */
    private function isPluginActive(string $pluginName, string $pluginSlug): bool
    {
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');

        if (is_plugin_active($pluginSlug)) {
            return true;
        }

        $this->addErrorNotice(
            $pluginName . ' is a required plugin.',
            $pluginName . ' needs to be installed & activated.'
        );

        return false;
    }

    /**
     * Checks whether required version of plugin is active
     *
     * @param string $pluginName       Plugin Name.
     * @param string $pluginSlug       Plugin Slug.
     * @param string $minPluginVersion Minimum version required of the plugin.
     *
     * @return bool
     * @since 1.0.0
     */
    private function isRequiredPluginVersionActive(
        string $pluginName,
        string $pluginSlug,
        string $minPluginVersion
    ): bool {
        $installedPluginVersion = $this->getPluginVersion($pluginSlug);
        $isRequiredPluginVersionActive = version_compare($installedPluginVersion, $minPluginVersion, '>=');

        if (1 == $isRequiredPluginVersionActive) {
            return true;
        }

        $this->addErrorNotice(
            "{$pluginName} {$minPluginVersion}+ is required.",
            "{$pluginName} {$installedPluginVersion} is installed."
        );

        return false;
    }

    /**
     * Returns the plugin version of passed plugin
     *
     * @param string $pluginSlug Plugin Slug of whose version needs to be retrieved.
     *
     * @return string Plugin Version
     * @since 1.0.0
     */
    private function getPluginVersion(string $pluginSlug): string
    {
        $pluginFilePath = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $pluginSlug;

        if (!file_exists($pluginFilePath)) {
            $pluginFilePath = WPMU_PLUGIN_DIR . DIRECTORY_SEPARATOR . $pluginSlug;
        }

        $pluginData = get_plugin_data($pluginFilePath, false, false);

        if (empty($pluginData['Version'])) {
            return '0.0';
        }

        return (string) $pluginData['Version'];
    }

    /**
     * Prints an error that the system requirements weren't met.
     *
     * @since    1.0.0
     * @TODO     : This should use our View class
     */
    public function showRequirementsErrors(): void
    {
        $errors = $this->errors;
        $adminErrorTemplate = Plugin_Name::getPluginTemplatesPath(
            ) . 'admin' . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR . 'requirements-error.php';
        require_once($adminErrorTemplate);
    }


    /**
     * @return RequirementsChecker
     */
    public static function getPluginRequirementsChecker(): RequirementsChecker
    {
        if (self::$requirementsChecker !== null) {
            return self::$requirementsChecker;
        }

        $requirementsConfig = include(Plugin_Name::getPluginPath(
            ) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'requirementsConfig.php');
        $requirementsData = apply_filters('plugin_name_minimum_requirements', $requirementsConfig);
        self::$requirementsChecker = new RequirementsChecker($requirementsData);

        return self::$requirementsChecker;
    }
}
