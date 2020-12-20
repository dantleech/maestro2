<?php

namespace Maestro\Core\Report;

use function array_pad;

class Table
{
    /**
     * @var array<string,array>
     */
    private $rows = [];

    /**
     * @param array<string,mixed> $data
     */
    public function mergeRow(string $name, array $data): void
    {
        if (!isset($this->rows[$name])) {
            $this->rows[$name] = [];
        }

        $this->rows[$name] = array_merge($this->rows[$name], $data);
    }

    public function rows(): array
    {
        $defaultRow = (function (array $headers) {
            return array_combine($headers, array_pad([], count($headers), null));
        })($this->headers());

        return array_map(function (array $row, string $group) use ($defaultRow) {
            return array_merge($defaultRow, ['group' => $group], $row);
        }, $this->rows, array_keys($this->rows));
    }

    /**
     * @return list<string>
     */
    public function headers(): array
    {
        if (empty($this->rows)) {
            return [];
        }

        return array_merge(
            ['group'],
            array_keys(
                array_reduce(
                    $this->rows,
                    function (array $sum, array $row) {
                        return array_merge($sum, $row);
                    },
                    []
                )
            )
        );
    }
}
