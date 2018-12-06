<?php

namespace WorldFactory\CookBook;

use Composer\Composer;
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
    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var IOInterface
     */
    protected $io;

    protected $modifiedPackages = [];

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
            PluginEvents::INIT => 'pluginDemoMethod',
            PackageEvents::POST_PACKAGE_INSTALL => 'modifyPackage',
            PackageEvents::POST_PACKAGE_UPDATE => 'modifyPackage',
            ScriptEvents::POST_AUTOLOAD_DUMP => 'autoloadGenerated'
        );
    }

    /**
     * @param Event $event
     */
    public function pluginDemoMethod(Event $event)
    {
        $this->io->write(PHP_EOL . '<options=bold>=============== CookBook ===============</>');
        $this->io->write(                  '<info>CookBook recipe installer is working! :)</info>');
        $this->io->write(          '<options=bold>========================================</>' . PHP_EOL);
    }

    public function modifyPackage(PackageEvent $event)
    {
        /** @var CompletePackage $package */
        $package = $event->getOperation()->getPackage();

        if (!in_array($package, $this->modifiedPackages)) {
            $this->modifiedPackages[] = $package;
        }
    }

    public function autoloadGenerated(Event $event)
    {
        require_once './vendor/autoload.php';

        $cookbook = new CookBook($this->composer, $this->io);

        /** @var CompletePackage $package */
        foreach ($this->modifiedPackages as $package) {
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