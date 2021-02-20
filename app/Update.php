<?php

namespace Plugin_Name\App;

use Plugin_Name\Includes\Plugin_Name;

/**
 * Class Update
 * Fired during plugin update.
 *
 */
class Update
{
    public static function update($upgradeObject, $options): void
    {
        $pluginPathName = Plugin_Name::getPluginPath();

        if ($options['action'] === 'update'
            && $options['type'] === 'plugin'
        ) {
            foreach ($options['plugins'] as $plugin) {
                if ($plugin == $pluginPathName) {
                    self::performUpdate();
                }
            }
        }
    }

    public static function performUpdate(): void
    {
        self::updateTo1();
    }

    private static function updateTo1(): void
    {
        //global $wpdb;
        //Update QUERY PROCESS
        //ALTER TABLE
    }


}
