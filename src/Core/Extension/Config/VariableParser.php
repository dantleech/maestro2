<?php

namespace Maestro\Core\Extension\Config;

class VariableParser
{
    /**
     * @param list<string> $cliVariables
     * @return array<string,mixed>
     */
    public function parse(array $cliVariables): array
    {
        return array_reduce(
            array_map(fn (mixed $v): mixed => $this->parseVariale($v), $cliVariables),
            function (array $parsed, array $pair): array {
                $parsed[$pair[0]] = $pair[1];
                return $parsed;
            },
            []
        );
    }

    private function parseVariale(string $v): mixed
    {
        return (function (string $key, string $value) {
            if (is_numeric($value)) {
                if (false !== strpos($value, '.')) {
                    return [$key, (float)$value];
                }
                return [$key, (int)$value];
            }

            return [$key, $value];
        })(...explode('=', $v, 2));
    }
}
