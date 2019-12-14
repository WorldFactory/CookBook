<?php

namespace WorldFactory\CookBook\Recipes;

use Exception;
use WorldFactory\CookBook\Foundations\AbstractRecipe;

class InstallSymfonyBundleRecipe extends AbstractRecipe
{
    const NAME = 'install-symfony-bundle';
    const PRIORITY = 3;
    const TARGET = 'config/bundles.php';
    const REGEX = '/^[ \t]+(?<class>(?>[A-Za-z0-9_]+\\\\)*(?>[A-Za-z0-9_]+))::class[ \t]*=>[ \t]*(?<env>\[.*\]),?$/';

    protected function getText() : string
    {
        $target = self::TARGET;

        return "Install Symfony bundle : \e[92m{$this->config['class']}\e[39;49m in \e[92m{$target}\e[39;49m... ";
    }

    protected function todo() : bool
    {
       return true;
    }

    protected function execute() : void
    {
        $target = self::TARGET;
        $class = $this->config['class'];

        if (file_exists($target)) {
            $packages = $this->parseBundleFile($target);
        } else {
            $packages = array();
        }

        if (!array_key_exists($class, $packages)) {
            $packages[$class] = "['all' => true]";
        }

        file_put_contents($target, $this->compileBundleFile($packages));
    }

    protected function parseBundleFile($filename)
    {
        if (!file_exists($filename)) {
            throw new Exception("File not found : $filename");
        }

        $content = file_get_contents($filename);
        $lines = explode(PHP_EOL, $content);

        $packages = array();

        foreach($lines as $line) {
            $results = null;
            if (preg_match(self::REGEX, $line, $results)) {
                $class = $results['class'];
                $env = $results['env'];

                $packages[$class] = $env;
            }
        }

        return $packages;
    }

    protected function compileBundleFile(array $packages)
    {
        $lines = array('<?php', '', 'return [');

        foreach($packages as $class => $env) {
            $lines[] = "    $class::class => $env,";
        }

        $lines[] = '];';

        return implode(PHP_EOL, $lines);
    }

    protected function getSchemaFilename() :? string
    {
        return __DIR__ . '/../../resources/schemas/install-symfony-bundle.json';
    }
}
