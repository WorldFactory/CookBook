<?php

namespace WorldFactory\CookBook\Recipes;

use Composer\IO\IOInterface;

abstract class AbstractRecipe
{
    /** @var array */
    protected $config;

    /**
     * @var IOInterface
     */
    protected $io;

    protected $package;

    public function __construct(IOInterface $io, array $config = [], $package)
    {
        $this->io = $io;
        $this->config = $config;
        $this->package = $package;
    }

    abstract public function run();
}