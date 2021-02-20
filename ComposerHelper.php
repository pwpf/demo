<?php
namespace Plugin_Name;

use Composer\Factory;
use Composer\Script\Event;
use Composer\Util\HttpDownloader;
use Exception;

/**
 * Class ComposerHelper
 */
class ComposerHelper
{
    /**
     * Prefix dependencies.
     *
     * @codeCoverageIgnore
     *
     * @param \Composer\Script\Event $event
     */
    public static function prefixDependencies(Event $event)
    {
        $io = $event->getIO();
        $io->write('Starting prefixing dependencies...');
        self::getPhpScoper($event);
        $io->write('Prefixing...');
        $event_dispatcher = $event->getComposer()->getEventDispatcher();
        $event_dispatcher->dispatchScript('prefix-dependencies', $event->isDevMode());
        self::removePhpScoper($event);
    }

    private static function getPhpScoper(Event $event)
    {
        $io = $event->getIO();
        $io->write('Getting php-scoper');
        $downloader = new HttpDownloader($event->getIO(), $event->getComposer()->getConfig());
        $phpScoperInfo = $downloader->get('https://api.github.com/repos/humbug/php-scoper/releases');
        $phpScoperInfo = json_decode($phpScoperInfo->getBody());
        $phpScoperVersion = '0.14.0';

        foreach ($phpScoperInfo as $version) {
            if (strpos($version->name, '0.14.') !== false) {
                $phpScoperVersion = $version->name;
            }
        }

        $url = "https://github.com/humbug/php-scoper/releases/download/$phpScoperVersion/php-scoper.phar";
        $io->write("php-scoper version: $phpScoperVersion");
        $phpScoper = $downloader->get($url)->getBody();
        file_put_contents(self::getRootPath() . 'php-scoper.phar', $phpScoper);
    }

    private static function removePhpScoper(Event $event)
    {
        $io = $event->getIO();
        $io->write('Removing php-scoper');
        unlink(self::getRootPath() . 'php-scoper.phar');
    }

    /**
     * Get project root directory.
     *
     * @return string
     */
    private static function getRootPath()
    {
        return realpath(dirname(Factory::getComposerFile())) . DIRECTORY_SEPARATOR;
    }

    /**
     * Clear vendor_prefixed directory.
     *
     * @codeCoverageIgnore
     *
     * @param Event $event Composer event that triggered this script.
     *
     * @return void
     * @throws \Throwable
     */
    public static function clearVendorPrefixed(Event $event)
    {
        $vendor_prefixed = realpath('vendor_prefixed');
        $dirs = self::findDirs($vendor_prefixed);

        if (!empty($dirs)) {
            $io = $event->getIO();
            $io->write('Clearing vendor_prefixed dir.');
            foreach ($dirs as $dir) {
                $io->write("Remove dir: $dir.");
                self::removeDir($dir);
            }
        }
    }

    /**
     * Move vendor directory.
     *
     * @codeCoverageIgnore
     *
     * @param Event $event Composer event that triggered this script.
     *
     * @return void
     * @throws \Throwable
     */
    public static function moveVendor(Event $event)
    {
        $io = $event->getIO();

        try {
            $source = realpath('vendor' . DIRECTORY_SEPARATOR . 'composer');
            $dest = realpath('vendor_prefixed') . DIRECTORY_SEPARATOR . 'composer';

            $io->write("Moving 'composer' to vendor_prefixed.");
            self::copyDir($source, $dest);
        } catch (Exception $e) {
            $io->write("Error during moving directory.");
            $io->write($e->getMessage());
            $io->write($e->getFile() . ':' . $e->getLine() . ' ' . $e->getCode());
            $io->write($e->getTraceAsString());
            exit(1);
        }

        $io->write("Moving autoload.php to vendor_prefixed.");
        copy(
            'vendor' . DIRECTORY_SEPARATOR . 'autoload.php',
            'vendor_prefixed' . DIRECTORY_SEPARATOR . 'autoload.php'
        );
        $io->write("Remove vendor directory.");
        self::removeDir('vendor');
    }

    /**
     * Remove directory.
     *
     * @param string|array $dirs
     *
     * @throws \Throwable
     */
    private static function removeDir($dirs): void
    {
        if (is_string($dirs)) {
            $dirs = [$dirs];
        }

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $objects = scandir($dir);

            if ($objects !== false) {
                foreach ($objects as $object) {
                    if ('.' === $object || '..' === $object) {
                        continue;
                    }

                    if (is_dir($dir . DIRECTORY_SEPARATOR . $object)) {
                        self::removeDir([$dir . DIRECTORY_SEPARATOR . $object]);
                    } else {
                        try {
                            unlink($dir . DIRECTORY_SEPARATOR . $object);
                        } catch (\Throwable $t) {
                            chmod($dir . DIRECTORY_SEPARATOR . $object, 0777);
                            try {
                                unlink($dir . DIRECTORY_SEPARATOR . $object);
                            } catch (\Throwable $t) {
                                throw $t;
                            }
                        }
                    }
                }
            }

            rmdir($dir);
        }
    }

    /**
     * Finds directories in path.
     *
     * @param string $dir
     *
     * @return array
     */
    private static function findDirs(string $dir): array
    {
        $dirs = [];

        if (!is_dir($dir)) {
            return $dirs;
        }

        $objects = scandir($dir);

        if ($objects !== false) {
            foreach ($objects as $object) {
                if ($object !== '.'
                    && $object !== '..'
                    && is_dir($dir . DIRECTORY_SEPARATOR . $object)
                ) {
                    $dirs[] = $dir . DIRECTORY_SEPARATOR . $object;
                }
            }
        }

        return $dirs;
    }

    /**
     * Copy directory.
     *
     * @param string $source
     * @param string $destination
     *
     * @throws \Exception
     */
    private static function copyDir(string $source, string $destination): void
    {
        if (is_dir($source)) {
            if (!file_exists($destination)) {
                if (!mkdir($destination, 0755, true) && !is_dir($destination)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $destination));
                }
            }

            $dir = opendir($source);

            while (($file = readdir($dir)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                if (is_dir($source . DIRECTORY_SEPARATOR . $file)) {

                    self::copyDir($source . DIRECTORY_SEPARATOR . $file, $destination . DIRECTORY_SEPARATOR . $file);

                } elseif (copy(
                        $source . DIRECTORY_SEPARATOR . $file,
                        $destination . DIRECTORY_SEPARATOR . $file
                    ) === false
                ) {
                    throw new Exception('Error while moving: ' . $source . DIRECTORY_SEPARATOR . $file);
                }
            }

            closedir($dir);
        }
    }
}