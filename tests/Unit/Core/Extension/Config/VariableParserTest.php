<?php

namespace Maestro\Tests\Unit\Core\Extension\Config;

use Generator;
use Maestro\Core\Extension\Config\VariableParser;
use PHPUnit\Framework\TestCase;

class VariableParserTest extends TestCase
{
    /**
     * @dataProvider provideParse
     */
    public function testParse(array $vars, array $expected)
    {
        self::assertSame($expected, (new VariableParser())->parse($vars));
    }

    public function provideParse(): Generator
    {
        yield 'empty' => [
            [
            ],
            [
            ]
        ];

        yield 'string' => [
            [
                'foo=bar',
            ],
            [
                'foo' => 'bar',
            ]
        ];

        yield 'string with =' => [
            [
                'foo=bar=car',
            ],
            [
                'foo' => 'bar=car',
            ]
        ];

        yield 'strings' => [
            [
                'foo=bar',
                'baz=bog',
            ],
            [
                'foo' => 'bar',
                'baz' => 'bog',
            ]
        ];

        yield 'int' => [
            [
                'foo=123',
            ],
            [
                'foo' => 123,
            ]
        ];

        yield 'float' => [
            [
                'foo=123.123',
            ],
            [
                'foo' => 123.123,
            ]
        ];
    }
}
