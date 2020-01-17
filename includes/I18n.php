<?php

namespace Plugin_Name\Includes;

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://example.com
 * @since      1.0.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 */
class I18n
{  // @codingStandardsIgnoreLine.

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
    public function loadPluginTextdomain()
    {
        load_plugin_textdomain(
            $this->domain,
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }

    /**
     * Set the domain equal to that of the specified domain.
     *
     * @param string $domain The domain that represents the locale of this plugin.
     *
     * @since    1.0.0.0
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

}
