<?php

namespace Maestro2\Core\Config\Exception;

use Exception;
use RuntimeException;

class CouldNotLoadConfig extends RuntimeException
{
    public static function couldNotReadFile(string $filename): self
    {
        return new self(sprintf(
            'Could not read file "%s"',
            $filename
        ));
    }

    public static function couldNotDecodeJson(Exception $exception): self
    {
        return new self($exception->getMessage(), 0, $exception);
    }

    public static function noConfigFileFound(array $tried): self
    {
        return new self(sprintf(
            'No config file detected, tried filenames "%s"',
            implode('", "', $tried)
        ));
    }
}
