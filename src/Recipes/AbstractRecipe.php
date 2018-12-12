<?php

namespace WorldFactory\CookBook\Recipes;

use Exception;
use Composer\IO\IOInterface;
use function file_exists;
use JsonSchema\Validator;
use Composer\Json\JsonValidationException;

abstract class AbstractRecipe
{
    const STATE_OK = "\e[30;102m OK \e[39;49m";
    const STATE_ERROR = "\e[30;103m ERROR \e[39;49m";

    const NAME = null;

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

        if ($this->getSchemaFilename() !== null) {
            $this->validate();
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
    protected function validate()
    {
        $schemaFile = realpath($this->getSchemaFilename());

        if (!file_exists($schemaFile)) {
            throw new Exception("File not found : '$schemaFile'.");
        }

        // Prepend with file:// only when not using a special schema already (e.g. in the phar)
        if (false === strpos($schemaFile, '://')) {
            $schemaFile = 'file://' . $schemaFile;
        }

        $schemaData = (object) ['$ref' => $schemaFile];

        $validator = new Validator();
        $config = $validator::arrayToObjectRecursive($this->config);
        $validator->check($config, $schemaData);

        if (!$validator->isValid()) {
            $errors = (array) $validator->getErrors();
            $error = current($errors);
            $errorText = "Error in recipe '{$this->getName()}'" . ($error['property'] ? " for property '{$error['property']}'" : '') . " : {$error['message']}";
            throw new JsonValidationException($errorText, $errors);
        }

        return true;
    }
}