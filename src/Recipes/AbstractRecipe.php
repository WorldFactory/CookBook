<?php

namespace WorldFactory\CookBook\Recipes;

use Exception;
use Composer\IO\IOInterface;

abstract class AbstractRecipe
{
    const STATE_OK = "\e[30;102m OK \e[39;49m";
    const STATE_ERROR = "\e[30;103m ERROR \e[39;49m";

    /** @var array */
    protected $config;

    /**
     * @var IOInterface
     */
    protected $io;

    protected $package;

    public function __construct(IOInterface $io, $package, array $config = [])
    {
        $this->io = $io;
        $this->config = $config;
        $this->package = $package;
    }

    /**
     * @throws Exception
     */
    public function run()
    {
        if ($this->todo()) {
            $this->io->write($this->getText(), false);
            try {
                $this->execute();
                $this->io->write(self::STATE_OK);
            } catch (Exception $exception) {
                $this->io->write(self::STATE_ERROR);
                throw $exception;
            }
        }
    }

    abstract protected function getName() : string;
    abstract protected function getText() : string;
    abstract protected function todo() : bool;
    abstract protected function execute() : void;
}