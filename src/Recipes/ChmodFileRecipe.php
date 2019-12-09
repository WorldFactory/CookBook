<?php

namespace WorldFactory\CookBook\Recipes;

use Exception;
use WorldFactory\CookBook\Foundations\AbstractRecipe;

class ChmodFileRecipe extends AbstractRecipe
{
    const NAME = 'chmod-file';

    protected function getText() : string
    {
        $target = $this->config['target'];
        $mode = $this->config['mode'];

        return "Chmod file : \e[92m{$target}\e[39;49m to \e[92m{$mode}\e[39;49m... ";
    }

    protected function todo() : bool
    {
       return true;
    }

    protected function execute() : void
    {
        $result = chmod($this->config['target'], self::stringToOctal($this->config['mode']));

        if (!$result) {
            throw new Exception("Unable to set chmod {$this->config['mode']} to this file : '{$this->config['target']}'.");
        }
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

    protected function getSchemaFilename() :? string
    {
        return __DIR__ . '/../../resources/schemas/chmod-file.json';
    }
}
