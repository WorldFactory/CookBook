<?php

namespace WorldFactory\CookBook\Misc;

use Composer\IO\IOInterface;

class RecipeFactory
{
    private $recipes = [];

    /**
     * @var IOInterface
     */
    private $io;

    public function __construct(IOInterface $io)
    {
        $this->io = $io;
    }

    public function addRecipe(string $name, string $className)
    {
        $this->recipes[$name] = $className;
    }

    public function hasRecipe(string $name)
    {
        return array_key_exists($name, $this->recipes);
    }

    public function buildRecipe(string $name, array $config = [], $package)
    {
        $className = $this->recipes[$name];

        $recipe = new $className($this->io, $config, $package);

        return $recipe;
    }
}
