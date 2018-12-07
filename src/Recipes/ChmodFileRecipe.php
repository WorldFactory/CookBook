<?php

namespace WorldFactory\CookBook\Recipes;

use Exception;

class ChmodFileRecipe extends AbstractRecipe
{
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
        chmod($this->config['target'], self::stringToOctal($this->config['mode']));
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
