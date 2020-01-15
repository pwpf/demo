<?php

namespace Plugin_Name;

use Composer\IO\IOInterface;
use Composer\Script\Event;

function getDirContents($dir, &$results = [])
{
	$files = scandir($dir);

	foreach ($files as $key => $value) {
		$path = realpath($dir . DIRECTORY_SEPARATOR . $value);
		if (!is_dir($path)) {
			//$results[] = $path; do nothing
			if (substr($path, -4) == '.php') {
				$results[] = $path;
			}
		} else {
			if ($value != "." && $value != "..") {
				getDirContents($path, $results);
			}
		}
	}

	return $results;
}

class OverloadClass
{

	/**
	 * Prefixes dependencies if composer install is ran with dev mode.
	 *
	 * Used in composer in the post-install script hook.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param \Composer\Script\Event $event Composer event that triggered this script.
	 *
	 * @return void
	 */
	public static function prefixDependencies(Event $event)
	{
		$io = $event->getIO();
		if (!$event->isDevMode()) {
			$io->write('Not prefixing dependencies.');
			return;
		}
		$io->write('Prefixing dependencies...');
		$event_dispatcher = $event->getComposer()->getEventDispatcher();
		$event_dispatcher->dispatchScript('prefix-dependencies', $event->isDevMode());
	}


}
