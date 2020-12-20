<?php

namespace Maestro\Tests\Unit\Composer;

use Generator;
use Maestro\Composer\ComposerJson;
use PHPUnit\Framework\TestCase;

class ComposerJsonTest extends TestCase
{
    /**
     * @dataProvider provideAutoloadPaths
     */
    public function testAutoloadPaths(array $composer, array $expectedPaths): void
    {
        $composer = ComposerJson::fromArray($composer);

        self::assertEquals($expectedPaths, $composer->autoloadPaths());
    }

    public function provideAutoloadPaths(): Generator
    {
        yield 'nothing 1' => [
            [
            ],
            [
            ]
        ];
        yield 'nothing 2' => [
            [
                'autoload-dev' => [
                ],
                'autoload' => [
                ],
            ],
            [
            ]
        ];
        yield 'autoload and autoload-dev' => [
            [
                'autoload-dev' => [
                    'psr-4' => [
                        'Foobar\\Tests' => 'tests/',
                    ],
                ],
                'autoload' => [
                    'psr-4' => [
                        'Foobar' => 'src/',
                    ],
                ]
            ],
            [
                'src/',
                'tests/',
            ]
        ];
    }
}
