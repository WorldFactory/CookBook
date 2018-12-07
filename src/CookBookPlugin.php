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
use Composer\Package\CompletePackage;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

class CookBookPlugin implements PluginInterface, EventSubscriberInterface
{
    public static $isHeaderDisplayed = false;

    const VERSION = 'v0.1';

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
            $this->io->write('<options=bold>CookBook</> - ' . self::VERSION . ' is activated.');

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

        $cookbook = new CookBook($this->composer, $this->io);

        /** @var CompletePackage $package */
        foreach ($this->installedPackages as $package) {
            $cookbook->installPackageRecipes($package);
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