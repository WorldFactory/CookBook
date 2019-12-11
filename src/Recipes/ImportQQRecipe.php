<?php

namespace WorldFactory\CookBook\Recipes;

use Exception;
use WorldFactory\CookBook\Foundations\AbstractRecipe;

class ImportQQRecipe extends AbstractRecipe
{
    const NAME = 'import-qq';
    const PRIORITY = 5;
    const TARGET = 'config/imports.json';

    protected function getText() : string
    {
        $target = self::TARGET;
        $source = $this->getSource();

        return "Import QQ lib : \e[92m{$source}\e[39;49m to \e[92m{$target}\e[39;49m... ";
    }

    protected function todo() : bool
    {
       return true;
    }

    protected function execute() : void
    {
        $source = $this->getSource();

        if (!file_exists($source)) {
            throw new Exception("Unable to execute recipe for package '{$this->package->getName()}', file not found : $source");
        }

        if (file_exists(self::TARGET)) {
            $imports = json_decode(file_get_contents(self::TARGET));
        } else {
            $imports = array();
        }

        if (!in_array($source, $imports)) {
            $imports[] = $source;

            file_put_contents(self::TARGET, json_encode($imports));
        }
    }

    protected function getSource() : string
    {
        return './vendor/' . $this->package->getName() . '/' . $this->config['source'];
    }

    protected function getSchemaFilename() :? string
    {
        return __DIR__ . '/../../resources/schemas/import-qq.json';
    }
}
