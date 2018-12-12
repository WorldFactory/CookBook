<?php

namespace WorldFactory\CookBook\Recipes;

class DisplayMessageRecipe extends AbstractRecipe
{
    const NAME = 'display-message';

    const COLOR_BORDER = "\e[43;90m";
    const COLOR_OUTPUT = "\e[43;30m";
    const COLOR_DEFAULT = "\e[0m";

    const CHAR_CORNER = '+';
    const CHAR_VLINE = '|';
    const CHAR_HLINE = '-';
    const CHAR_SPACE = ' ';

    const DISPLAY_EXECUTION = false;

    protected function getText() : string
    {
        return null;
    }

    protected function todo() : bool
    {
       return true;
    }

    protected function execute() : void
    {
        $output = $this->config['output'];
        $messages = is_array($output) ? $output : [$output];

        $this->displayMessages($messages);
    }

    protected function displayMessages(array $messages)
    {
        $max = $this->getLongestLineLength($messages);

        $line = self::COLOR_BORDER . self::CHAR_CORNER . str_repeat(self::CHAR_HLINE, $max + 2) . self::CHAR_CORNER . self::COLOR_DEFAULT;
        $vBorder = self::COLOR_BORDER . self::CHAR_VLINE . self::COLOR_DEFAULT;

        $this->io->write($line);

        foreach ($messages as $message) {
            $message = $message . str_repeat(self::CHAR_SPACE, $max - strlen($message));
            $message = self::CHAR_SPACE . $message . self::CHAR_SPACE;
            $message = self::COLOR_OUTPUT . $message . self::COLOR_DEFAULT;

            $this->io->write($vBorder . $message . $vBorder);
        }

        $this->io->write($line);
    }

    protected function getLongestLineLength(array $messages)
    {
        $max = 0;
        foreach ($messages as $message) {
            if (strlen($message) > $max) {
                $max = strlen($message);
            }
        }

        return $max;
    }

    protected function getSchemaFilename() :? string
    {
        return __DIR__ . '/../../resources/schemas/display-message.json';
    }
}
