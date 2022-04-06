<?php

namespace WorldFactory\CookBook\Misc;

use Composer\Json\JsonFile as ComposerJsonFile;
use Composer\Json\JsonValidationException;
use JsonSchema\Validator;

class JsonFile extends ComposerJsonFile
{
    const COMPOSER_SCHEMA_PATH = '/../../../res/composer-schema.json';

    /**
     * @inheritdoc
     */
    public function validateSchema(int $schema = self::STRICT_SCHEMA, ?string $schemaFile = null) : bool
    {
        $content = file_get_contents($this->getPath());
        $data = json_decode($content);

        if (null === $data && 'null' !== $content) {
            self::validateSyntax($content, $this->getPath());
        }

        if (null === $schemaFile) {
            $schemaFile = __DIR__ . self::COMPOSER_SCHEMA_PATH;
        }

        // Prepend with file:// only when not using a special schema already (e.g. in the phar)
        if (false === strpos($schemaFile, '://')) {
            $schemaFile = 'file://' . $schemaFile;
        }

        $schemaData = (object) array('$ref' => $schemaFile);

        if ($schema === self::LAX_SCHEMA) {
            $schemaData->additionalProperties = true;
            $schemaData->required = array();
        }

        $validator = new Validator();
        $validator->check($data, $schemaData);

        // TODO add more validation like check version constraints and such, perhaps build that into the arrayloader?

        if (!$validator->isValid()) {
            $errors = array();
            foreach ((array) $validator->getErrors() as $error) {
                $errors[] = ($error['property'] ? $error['property'].' : ' : '').$error['message'];
            }
            throw new JsonValidationException('"'.$this->getPath().'" does not match the expected JSON schema', $errors);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public static function parseJson(?string $json, ?string $file = null)
    {
        if (null === $json) {
            return;
        }
        $data = json_decode($json, false);
        if (null === $data && JSON_ERROR_NONE !== json_last_error()) {
            self::validateSyntax($json, $file);
        }

        return $data;
    }
}