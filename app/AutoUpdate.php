<?php

namespace Plugin_Name\App;

// If this file is called directly, abort.
defined('WPINC') or die;

use Exception;
use JsonException;
use Plugin_Name\Includes\Plugin_Name;
use RuntimeException;
use stdClass;

class AutoUpdate
{
    /**
     * URL to plugin info JSON
     *
     * For test purpose use in eg.: https://httpstat.us/500?sleep=60
     */
    private const REMOTE = '__PUT__HERE_URL_AUTO_UPDATE_SERVER_';

    /**
     * Main function
     */
    public static function register(): void
    {
        // Not an admin of page -> do nothing
        if (!current_user_can('administrator')) {
            return;
        }

        add_filter('plugins_api', [self::class, 'pluginInfo'], 20, 3);
        add_filter('pre_set_site_transient_update_plugins', [self::class, 'pushUpdate'], 10, 1);
        add_action('upgrader_process_complete', [self::class, 'afterUpdate'], 10, 2);
    }

    /**
     * @return array|null
     * @throws \RuntimeException
     */
    private static function getRemote(): ?array
    {
        if (true === get_transient('update_block_' . Plugin_Name::PLUGIN_ID)) {
            throw new RuntimeException('Update server down? Wait till next check.');
        }

        $remote = wp_remote_get(self::REMOTE, ['timeout' => 5, 'headers' => ['Accept' => 'application/json']]);

        if (is_wp_error($remote)) {
            throw new RuntimeException($remote->get_error_message());
        }

        if ($remote['response']['code'] !== 200) {
            throw new RuntimeException('Remote response: ' . $remote['response']['message']);
        }

        try {
            $remote['body'] = json_decode($remote['body'], true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('Remote response error: ' . $e->getMessage());
        }

        return $remote['body'];
    }

    /**
     * @param $res
     * @param $action
     * @param $args
     *
     * @return false|\stdClass
     */
    public static function pluginInfo($res, $action, $args)
    {
        // do nothing if this is not about getting plugin information
        // and do nothing if it is not our plugin
        if ('plugin_information' !== $action or Plugin_Name::PLUGIN_ID !== $args->slug) {
            return false;
        }

        try {
            $remote = self::getRemote();
            $res = new stdClass();
            $res->name = $remote['name'];
            $res->slug = Plugin_Name::PLUGIN_ID;
            $res->version = $remote['version'];
            $res->tested = $remote['tested'];
            $res->requires = $remote['requires'];
            $res->author = $remote['author'];
            $res->author_profile = 'https://YOUR_URL';
            $res->download_link = $remote['download_url'];
            $res->trunk = $remote['download_url'];
            $res->last_updated = $remote['last_updated'];
            $res->sections = [
                'description' => $remote['sections']['description'] ?? '',
                'installation' => $remote['sections']['installation'] ?? '',
                'changelog' => $remote['sections']['changelog'] ?? '',
                // you can add your custom sections (tabs) here
            ];

            // in case you want the screenshots tab, use the following HTML format for its content:
            // <ol><li><a href="IMG_URL" target="_blank" rel="noopener noreferrer"><img src="IMG_URL" alt="CAPTION" /></a><p>CAPTION</p></li></ol>
            if (!empty($remote['sections']['screenshots'])) {
                $res->sections['screenshots'] = $remote['sections']['screenshots'];
            }
        } catch (Exception $e) {


            add_action( 'admin_notices',  function() use ($e) {
                ?>
                <div class="error notice">
                    <p><?php _e($e->getMessage(), 'my_plugin_textdomain' ); ?></p>
                </div>
                <?php
            } );

            set_transient('update_block_' . Plugin_Name::PLUGIN_ID, true, 300);
        }

        return $res;
    }

    public static function pushUpdate($transient)
    {
        $plugin = Plugin_Name::PLUGIN_ID . '/plugin-name.php';

        if (!is_object($transient) or !isset($transient->checked[$plugin])) {
            return $transient;
        }

        if (!isset($transient->response) or !is_array($transient->response)) {
            $transient->response = [];
        }

        if (isset($transient->response[$plugin])) {
            return $transient;
        }

        try {
            $remote = get_transient(Plugin_Name::PLUGIN_ID . '_update');

            if (false === $remote) {
                $remote = self::getRemote();
                set_transient(Plugin_Name::PLUGIN_ID . '_update', $remote, 120);
            }

            if (version_compare(Plugin_Name::getPluginVersion(), $remote['version'], '<')
                and version_compare($remote['requires'], get_bloginfo('version'), '<')
            ) {
                $res = new stdClass();
                $res->slug = Plugin_Name::PLUGIN_ID;
                $res->plugin = $plugin;
                $res->new_version = $remote['version'];
                $res->tested = $remote['tested'];
                $res->package = $remote['download_url'];
                $transient->response[$plugin] = $res;
            }
        } catch (Exception $e) {

            add_action( 'admin_notices',  function() use ($e) {
                ?>
                <div class="error notice">
                    <p><?php _e($e->getMessage(), 'my_plugin_textdomain' ); ?></p>
                </div>
                <?php
            } );

            set_transient('update_block_' . Plugin_Name::PLUGIN_ID, true, 300);
        }

        return $transient;
    }

    public static function afterUpdate($upgraderObject, $options): void
    {
        if ($options['action'] === 'update' and $options['type'] === 'plugin') {
            // just clean the cache when new plugin version is installed
            delete_transient('update_' . Plugin_Name::PLUGIN_ID);
            delete_transient('update_block_' . Plugin_Name::PLUGIN_ID);
        }
    }
}
