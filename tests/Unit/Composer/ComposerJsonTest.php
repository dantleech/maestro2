<?php

namespace Maestro\Tests\Unit\Composer;

use Closure;
use Generator;
use Maestro\Composer\ComposerJson;
use Maestro\Composer\ComposerPackages;
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

    /**
     * @dataProvider providePackages
     */
    public function testPackages(array $composer, Closure $assertion): void
    {
        $composer = ComposerJson::fromArray($composer);
        $assertion($composer->packages());
    }

    /**
     * @return Generator<mixed>
     */
    public function providePackages(): Generator
    {
        yield 'no packages' => [
            [
            ],
            fn (ComposerPackages $packages) => self::assertCount(0, $packages)
        ];

        yield 'require and require-dev' => [
            [
                'require' => [
                    'example/foobar' => '1.0',
                ],
                'require-dev' => [
                    'example/test' => '^0.1',
                ]
            ],
            function (ComposerPackages $packages) {
                self::assertCount(2, $packages);
                self::assertEquals('example/foobar', $packages->get('example/foobar')->name());
                self::assertEquals('1.0', $packages->get('example/foobar')->version());
            }
        ];
    }

    public function testBranchAliasNoBranchAlias(): void
    {
        $composer = ComposerJson::fromArray([]);
        self::assertEquals([], $composer->branchAliases());
    }

    public function testBranchAlias(): void
    {
        $composer = ComposerJson::fromArray([
            'extra' => [
                'branch-alias' => [
                    'dev-master' => '1.9',
                ],
            ],
        ]);
        self::assertEquals([
            'dev-master' => '1.9',
        ], $composer->branchAliases());
    }
}
