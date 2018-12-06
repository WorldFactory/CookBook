<?php

namespace WorldFactory\CookBook\Recipes;

class CopyFileRecipe extends AbstractRecipe
{
    public function run()
    {
        $target = $this->config['target'];
        $source = './vendor/' . $this->package->getName() . '/' . $this->config['source'];
        $force = array_key_exists('force', $this->config) && ($this->config['force'] === true);

        if (!file_exists($target) || $force) {
            $this->io->write("# Copy file : \e[92m{$source}\e[39;49m to \e[92m{$target}\e[39;49m...");
            copy($source, $target);
        }
    }
}