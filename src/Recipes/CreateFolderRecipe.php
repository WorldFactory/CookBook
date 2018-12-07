<?php

namespace WorldFactory\CookBook\Recipes;

use Exception;

class CreateFolderRecipe extends AbstractRecipe
{
    protected function getText() : string
    {
        $target = $this->config['target'];

        return "Create folder : \e[92m{$target}\e[39;49m... ";
    }

    protected function todo() : bool
    {
       return !file_exists($this->config['target']);
    }

    protected function execute() : void
    {
        $mode = $this->config['mode'] ?? "765";
        mkdir($this->config['target'], self::stringToOctal($mode), true);
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
