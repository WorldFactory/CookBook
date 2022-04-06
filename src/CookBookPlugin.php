<?php

namespace WorldFactory\CookBook;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\Event;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Json\JsonValidationException;
use Composer\Package\CompletePackage;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

class CookBookPlugin implements PluginInterface, EventSubscriberInterface
{
    public static $isHeaderDisplayed = false;

    const VERSION = 'v1.0.0';

    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var IOInterface
     */
    protected $io;

    protected $installedPackages = [];
    protected $updatedPackages = [];
    protected $deletedPackages = [];

    /**
     * @param Composer $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
        $this->composer = null;
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        // TODO: Implement uninstall() method.
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            PluginEvents::INIT => 'pluginInit',
            PackageEvents::POST_PACKAGE_INSTALL => 'installPackage',
            PackageEvents::POST_PACKAGE_UPDATE => 'updatePackage',
            PackageEvents::POST_PACKAGE_UNINSTALL => 'removePackage',
            ScriptEvents::POST_AUTOLOAD_DUMP => 'autoloadGenerated'
        );
    }

    /**
     * @param Event $event
     */
    public function pluginInit(Event $event)
    {
        if (!CookBookPlugin::$isHeaderDisplayed) {
            $this->io->write('<options=bold>CookBook</> - ' . self::VERSION . ' is in da place!');

            CookBookPlugin::$isHeaderDisplayed = true;
        }
    }

    public function installPackage(PackageEvent $event)
    {
        /** @var InstallOperation $operation */
        $operation = $event->getOperation();

        /** @var CompletePackage $package */
        $package = $operation->getPackage();

        if (!in_array($package, $this->installedPackages)) {
            $this->installedPackages[] = $package;
        }
    }

    public function updatePackage(PackageEvent $event)
    {
        /** @var UpdateOperation $operation */
        $operation = $event->getOperation();

        /** @var CompletePackage $package */
        $package = $operation->getTargetPackage();

        if (!in_array($package, $this->updatedPackages)) {
            $this->updatedPackages[] = $package;
        }
    }

    public function removePackage(PackageEvent $event)
    {
        /** @var UninstallOperation $operation */
        $operation = $event->getOperation();

        /** @var CompletePackage $package */
        $package = $operation->getPackage();

        if (!in_array($package, $this->deletedPackages)) {
            $this->deletedPackages[] = $package;
        }
    }

    public function autoloadGenerated(Event $event)
    {
        require_once './vendor/autoload.php';

        if (($this->composer !== null) && class_exists('WorldFactory\CookBook\CookBook')) {
            $cookbook = new CookBook($this->composer, $this->io);

            $this->installPackageRecipes($cookbook, $this->installedPackages);
            $this->installPackageRecipes($cookbook, $this->updatedPackages);

            $cookbook->run();
        } else {
            $this->io->write('<options=bold>CookBook</> left the place.');
        }
    }

    /**
     * @param CookBook $cookbook
     * @param array $packages
     */
    private function installPackageRecipes(CookBook $cookbook, array $packages)
    {
        /** @var CompletePackage $package */
        foreach ($packages as $package) {
            try {
                $cookbook->installPackageRecipes($package);
            } catch (JsonValidationException $exception) {
                $this->io->write("<error>Recipe validation errors in {$package->getName()} :</error>");
                foreach($exception->getErrors() as $error) {
                    if (is_array($error)) {
                        $this->io->write("-> <error>{$exception->getMessage()} : {$error['message']}</error>");
                    } else {
                        $this->io->write("-> <error>{$exception->getMessage()} : $error</error>");
                    }
                }
            }
        }
    }

    private function dumpPackage(CompletePackage $package)
    {
        $dumper = new CliDumper();
        $cloner = new VarCloner();

        $cloner->setMaxItems(255);

        $dumper->dump($cloner->cloneVar($package));
    }
}