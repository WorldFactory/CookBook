<?php
namespace LJSL\QQ\Misc;

use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Script\Event;
use Exception;
use function file_exists;

class Hook
{
    const MANIFEST_SRC = __DIR__ . '/../../manifest.json';

    /**
     * @param Event $event
     * @throws \Exception
     */
    public static function installShortcuts(Event $event)
    {
        /** @var IOInterface $io */
        $io = $event->getIO();

        $io->write("Install QQ Recipe...");

        $file = new JsonFile(self::MANIFEST_SRC);
        $data = $file->read();

        try {
            foreach ($data['actions'] as $action) {
                switch ($action['type']) {
                    case 'copy-file':
                        self::copyFile($action, $io);
                        break;
                    case 'create-folder':
                        self::createFolder($action, $io);
                        break;
                    case 'chmod-file':
                        self::chmodFile($action, $io);
                        break;
                    case 'append-bashrc':
                        self::appendBashrc($action, $io);
                        break;
                    case 'append-gitignore':
                        self::appendGitignore($action, $io);
                        break;
                    case 'append-env':
                        self::appendEnv($action, $io);
                        break;
                    case 'append-composer-script':
                        self::appendComposerScript($action, $io);
                        break;
                    default:
                        throw new Exception("Unknown recipe type : '{$action['type']}'.");
                }
            }
        } catch (\Exception $e) {
            $io->writeError(" \e[91mERROR\e[39;49m !!");

            throw $e;
        }

        $io->write("For unix user, add this command in your \e[95m.bashrc\e[39;49m file : \e[30;43malias qq=\"./qq.sh\"\e[39;49m");
    }

    private static function copyFile(array $config, IOInterface $io)
    {
        if (!file_exists($config['target'])) {
            $io->write("# Copy file : \e[92m{$config['source']}\e[39;49m to \e[92m{$config['target']}\e[39;49m...");
            copy($config['source'], $config['target']);
        }
    }

    private static function createFolder(array $config, IOInterface $io)
    {
        if (!file_exists($config['target'])) {
            $io->write("# Create folder : \e[92m{$config['target']}\e[39;49m...");
            $mode = $config['mode'] ?? "765";
            mkdir($config['target'], self::stringToOctal($mode), true);
        }
    }

    private static function chmodFile(array $config, IOInterface $io)
    {
        if (file_exists($config['target'])) {
            $io->write("# Chmod file : \e[92m{$config['target']}\e[39;49m to \e[92m{$config['mode']}\e[39;49m...");
            chmod($config['target'], self::stringToOctal($config['mode']));
        }
    }

    private static function appendBashrc(array $config, IOInterface $io)
    {

    }

    private static function appendGitignore(array $config, IOInterface $io)
    {

    }

    private static function appendEnv(array $config, IOInterface $io)
    {

    }

    private static function appendComposerScript(array $config, IOInterface $io)
    {

    }

    /**
     * @param string $value
     * @return int
     * @throws Exception
     */
    private static function stringToOctal(string $value) : int
    {
        if (!preg_match('/^[0-9]+$/', $value)) {
            throw new Exception("Unvalid octal value : '$value'.");
        }

        return octdec((int) $value);
    }
}
