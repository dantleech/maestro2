<?php

namespace Maestro\Core\Extension\Command;

use RuntimeException;

class Cast
{
    public static function stringOrNull(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }

        return self::string($value);
    }

    public static function string(mixed $value): string
    {
        if (!is_string($value)) {
            throw new RuntimeException(sprintf(
                'Expected string or null, got "%s"',
                self::typeName($value)
            ));
        }

        return $value;
    }

    private static function typeName(mixed $value):string
    {
        return is_object($value) ? $value::class : gettype($value);
    }
}
