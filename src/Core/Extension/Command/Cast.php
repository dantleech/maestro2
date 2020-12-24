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

    /**
     * @return list<string>
     */
    public static function arrayOfStrings(mixed $values): array
    {
        $values = self::array($values);
        return array_values(array_map(fn (mixed $v) => self::string($v), $values));
    }

    private static function array(mixed $values): array
    {
        if (!is_array($values)) {
            throw new RuntimeException(sprintf(
                'Expected array, got "%s"',
                self::typeName($values)
            ));
        }

        return $values;
    }

    /**
     * @return list<mixed>
     */
    public static function list(mixed $values): array
    {
        return array_values(self::array($values));
    }

    /**
     * @return list<string>
     */
    public static function listOfStrings(mixed $values): array
    {
        $values = self::list($values);
        return array_values(array_map(fn (mixed $v) => self::string($v), $values));
    }
}
