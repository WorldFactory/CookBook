<?php

namespace WorldFactory\CookBook;

use Exception;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\CompletePackage;
use WorldFactory\CookBook\Misc\JsonFile;
use WorldFactory\CookBook\Misc\RecipeFactory;
use WorldFactory\CookBook\Foundations\AbstractRecipe;

class CookBook
{
    const RECIPE_FILE = './vendor/%s/recipe.json';

    const RECIPE_CLASSES = [
        'copy-file' => 'WorldFactory\CookBook\Recipes\CopyFileRecipe',
        'chmod-file' => 'WorldFactory\CookBook\Recipes\ChmodFileRecipe',
        'create-folder' => 'WorldFactory\CookBook\Recipes\CreateFolderRecipe',
        'display-message' => 'WorldFactory\CookBook\Recipes\DisplayMessageRecipe'
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

    private $recipes = [];

    public function __construct(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;

        $this->recipeFactory = new RecipeFactory($io);

        foreach (self::RECIPE_CLASSES as $name => $className) {
            $this->recipeFactory->addRecipe($name, $className);
        }
    }

    private function displayHeader()
    {
        $this->io->write(PHP_EOL . '<options=bold>+--------------</> <error>CookBook</> <options=bold>--------------+</>');
        $this->io->write('<options=bold>|</> <info>CookBook recipe installer is working</info> <options=bold>|</>');
        $this->io->write('<options=bold>+--------------------------------------+</>');
    }

    public function installPackageRecipes(CompletePackage $package)
    {
        $rawRecipes = $this->getRawRecipes($package->getName());

        foreach ($rawRecipes as $rawRecipe) {
            $this->recipes[] = $this->recipeFactory->buildRecipe($package, $rawRecipe);
        }
    }

    public function run()
    {
        if (count($this->recipes) > 0) {
            $this->displayHeader();

            try {
                /** @var AbstractRecipe $recipe */
                foreach ($this->recipes as $recipe) {
                    $recipe->run();
                }
            } catch (Exception $exception) {
                $this->io->write("\e[91mERROR\e[39;49m : " . $exception->getMessage());
            }
        }
    }

    /**
     * @param string $packageName
     * @return array
     * @throws \Composer\Json\JsonValidationException
     */
    private function getRawRecipes(string $packageName)
    {
        $rawRecipes = [];
        $filename = sprintf(self::RECIPE_FILE, $packageName);
        $file = new JsonFile($filename, null, $this->io);
        if ($file->exists()) {
            if ($file->validateSchema(JsonFile::STRICT_SCHEMA, __DIR__ . '/../resources/schemas/root.json')) {
                $config = $file->read();

                if ($config->recipe->type === 'cookbook') {
                    $rawRecipes = $config->actions;
                } elseif ($this->io->isVeryVerbose()) {
                    $this->io->write("$packageName has recipe.json file, but not of cookbook type.");
                }
            } elseif ($this->io->isVeryVerbose()) {
                $this->io->write("$packageName has invalid recipe.json file.");
            }
        } elseif ($this->io->isVeryVerbose()) {
            $this->io->write("$packageName has not recipe.json file.");
        }

        return $rawRecipes;
    }
}