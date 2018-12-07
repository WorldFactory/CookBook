<?php

namespace WorldFactory\CookBook\Recipes;

use Exception;

class CopyFileRecipe extends AbstractRecipe
{
    protected function getText() : string
    {
        $target = $this->config['target'];
        $source = $this->getSource();

        return "Copy file : \e[92m{$source}\e[39;49m to \e[92m{$target}\e[39;49m... ";
    }

    protected function todo() : bool
    {
       return (!file_exists($this->config['target']) || $this->inForce());
    }

    protected function execute() : void
    {
        copy($this->getSource(), $this->config['target']);
    }

    protected function getSource() : string
    {
        return './vendor/' . $this->package->getName() . '/' . $this->config['source'];
    }

    protected function inForce() : bool
    {
        return array_key_exists('force', $this->config) && ($this->config['force'] === true);
    }
}
