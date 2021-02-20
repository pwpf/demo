<?php

namespace Plugin_Name\Includes;


defined('WPINC') or die;

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://example.com
 * @since      1.0.0.0
 *
 */
class I18n
{
    // @codingStandardsIgnoreLine.
    /**
     * The domain specified for this plugin.
     *
     * @since    1.0.0.0
     * @access   private
     * @var      string $domain The domain identifier for this plugin.
     */
    private $domain;

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0.0
     */
    public function loadPluginTextDomain(): void
    {
        load_plugin_textdomain(
            $this->domain,
            false,
            Plugin_Name::getPluginDirName() . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Languages'
        );
    }

    /**
     * Set the domain equal to that of the specified domain.
     *
     * @param string $domain The domain that represents the locale of this plugin.
     *
     * @since    1.0.0.0
     */
    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }
}
