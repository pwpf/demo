<?php

namespace Plugin_Name\Includes;

/**
 * Checks whether plugin's requirements are being met or not
 */
class RequirementsChecker
{

    /**
     * Holds minimum php version for plugin if not defined in `requirements.php`.
     *
     * @var string
     * @since 1.0.0
     */
    private $minPhpVersion = '7.0';

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
    public function __construct($requirementsData)
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
    public function requirementsMet()
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

    /**
     * Checks if Installed PHP Version is higher than required PHP Version
     *
     * @return bool
     * @since 1.0.0
     */
    private function isPhpVersionDependencyMet()
    {
        $isRequiredPhpVersionInstalled = version_compare(PHP_VERSION, $this->minPhpVersion, '>=');

        if (1 == $isRequiredPhpVersionInstalled) {
            return true;
        }

        $this->addErrorNotice(
            'PHP ' . $this->minPhpVersion . '+ is required',
            'You\'re running version ' . PHP_VERSION
        );

        return false;
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
    private function addErrorNotice($errorMessage, $supportiveInformation)
    {
        $this->errors[] = (object)[
            'error_message' => $errorMessage,
            'supportive_information' => $supportiveInformation,
        ];
    }

    /**
     * Checks if Installed WP Version is higher than required WP Version
     *
     * @return bool
     * @since 1.0.0
     */
    private function isWpVersionDependencyMet()
    {
        global $wpVersion;
        $isRequiredWpVersionInstalled = version_compare($wpVersion, $this->minWpVersion, '>=');

        if (1 == $isRequiredWpVersionInstalled) {
            return true;
        }

        $this->addErrorNotice(
            'WordPress ' . $this->minWpVersion . '+ is required',
            'You\'re running version ' . $wpVersion
        );

        return false;
    }

    /**
     * Checks if Multisite Dependencies are met
     *
     * @return bool
     * @since 1.0.0
     */
    private function isWpMultisiteDependencyMet()
    {
        $isWpMultisiteDependencyMet = is_multisite() && (false === $this->isMultisiteCompatible) ? false : true;

        if (false == $isWpMultisiteDependencyMet) {
            $this->addErrorNotice(
                'Your site is set up as a Network (Multisite)',
                'This plugin is not compatible with multisite environment'
            );
        }

        return $isWpMultisiteDependencyMet;
    }

    /**
     * Checks whether all required plugins are installed & active with proper versions.
     *
     * @return bool
     * @since 1.0.0
     */
    private function areRequiredPluginsDependencyMet()
    {
        $pluginDependencyMet = true;

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
            $pluginDependencyMet = false;
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
            $pluginDependencyMet = false;
        }

        return $pluginDependencyMet;
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
    private function isPluginActive($pluginName, $pluginSlug)
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
    private function isRequiredPluginVersionActive($pluginName, $pluginSlug, $minPluginVersion)
    {
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
    private function getPluginVersion($pluginSlug)
    {
        $pluginFilePath = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $pluginSlug;

        if (!file_exists($pluginFilePath)) {
            $pluginFilePath = WPMU_PLUGIN_DIR . DIRECTORY_SEPARATOR . $pluginSlug;
        }

        $pluginData = get_plugin_data($pluginFilePath, false, false);

        if (empty($pluginData['Version'])) {
            return '0.0';
        }

        return $pluginData['Version'];
    }

    /**
     * Prints an error that the system requirements weren't met.
     *
     * @since    1.0.0
     */
    public function showRequirementsErrors()
    {
        $errors = $this->errors;
        require_once(dirname(dirname(__FILE__)) . '/app/templates/admin/errors/requirements-error.php');
    }
}
