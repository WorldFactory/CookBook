<?php

namespace WorldFactory\CookBook;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\CompletePackage;
use Composer\Json\JsonFile;
use WorldFactory\CookBook\Misc\RecipeFactory;

class CookBook
{
    const RECIPE_FILE = './vendor/%s/recipe.json';

    const RECIPE_CLASSES = [
        'copy-file' => 'WorldFactory\CookBook\Recipes\CopyFileRecipe'
    ];

    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var IOInterface
     */
    private $io;

    /** @var RecipeFactory */
    private $recipeFactory;

    public function __construct(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;

        $this->recipeFactory = new RecipeFactory($io);

        foreach (self::RECIPE_CLASSES as $name => $className) {
            $this->recipeFactory->addRecipe($name, $className);
        }
    }

    public function installPackageRecipes(CompletePackage $package)
    {
        $rawRecipes = $this->getRawRecipes($package->getName());

        foreach ($rawRecipes as $rawRecipe) {
            $this->recipeFactory->buildRecipe($rawRecipe['type'], $rawRecipe, $package)->run();
        }
    }

    private function getRawRecipes(string $packageName)
    {
        $rawRecipes = [];
        $filename = sprintf(self::RECIPE_FILE, $packageName);
        $file = new JsonFile($filename, null, $this->io);
        if ($file->exists()) {
            $config = $file->read();
            if (is_array($config) && array_key_exists('actions', $config)) {
                $rawRecipes = $config['actions'];
            }
        }

        return $rawRecipes;
    }
}