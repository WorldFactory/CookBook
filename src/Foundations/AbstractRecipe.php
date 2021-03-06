<?php

namespace WorldFactory\CookBook\Foundations;

use stdClass;
use Exception;
use function file_exists;
use Composer\Package\PackageInterface;
use Composer\IO\IOInterface;
use Composer\Json\JsonValidationException;
use JsonSchema\Validator;

abstract class AbstractRecipe
{
    const STATE_OK = "\e[30;102m OK \e[39;49m";
    const STATE_ERROR = "\e[30;103m ERROR \e[39;49m";

    const NAME = null;

    /** @var stdClass */
    protected $config;

    /** @var IOInterface */
    protected $io;

    /** @var PackageInterface */
    protected $package;

    /**
     * AbstractRecipe constructor.
     * @param IOInterface $io
     * @param PackageInterface $package
     * @param stdClass $config
     * @throws Exception
     * @throws JsonValidationException
     */
    public function __construct(IOInterface $io, PackageInterface $package, stdClass $config)
    {
        $this->io = $io;
        $this->package = $package;

        if ($this->validate($config)) {
            $this->config = json_decode(json_encode($config), true);
        }
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

    public function getName() : string
    {
        if (static::NAME === null) {
            throw new \LogicException("Recipe must have valid name.");
        }

        return static::NAME;
    }

    public function getPriority() : int
    {
        if (static::PRIORITY === null) {
            throw new \LogicException("Recipe must have valid priority.");
        }

        return static::PRIORITY;
    }

    abstract protected function getText() : string;
    abstract protected function todo() : bool;
    abstract protected function execute() : void;

    protected function getSchemaFilename() :? string
    {
        return null;
    }

    /**
     * @return bool
     * @throws Exception
     * @throws JsonValidationException
     */
    protected function validate(stdClass $config) : bool
    {
        $schemaFile = realpath($this->getSchemaFilename());

        if (!file_exists($schemaFile)) {
            throw new Exception("File not found : '$schemaFile'.");
        }

        $schemaData = (object) ['$ref' => "file://$schemaFile"];

        $validator = new Validator();
        $validator->validate($config, $schemaData);

        if (!$validator->isValid()) {
            $errors = (array) $validator->getErrors();
            $errorText = "Error in recipe '{$this->getName()}'";
            throw new JsonValidationException($errorText, $errors);
        }

        return true;
    }
}