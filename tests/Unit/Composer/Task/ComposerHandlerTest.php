<?php

namespace Maestro\Tests\Unit\Composer\Task;

use Maestro\Composer\ComposerPackage;
use Maestro\Composer\Fact\ComposerJsonFact;
use Maestro\Core\Fact\PhpFact;
use Maestro\Core\Process\Exception\ProcessFailure;
use Maestro\Core\Process\ProcessResult;
use Maestro\Composer\Task\ComposerTask;
use Maestro\Core\Task\Context;
use Maestro\Tests\Unit\Core\Task\HandlerTestCase;

class ComposerHandlerTest extends HandlerTestCase
{
    protected function defaultContext(): Context
    {
        return parent::defaultContext()->merge(Context::fromFacts(
            new PhpFact(
                phpBin: 'php3',
            ),
        ));
    }

    public function testLeavesFactWithCreated(): void
    {
        $context = $this->runTask(new ComposerTask(
            require: [
                'foobar/barfoo' => '^1.0',
            ]
        ));

        self::assertInstanceOf(ComposerJsonFact::class, $context->fact(ComposerJsonFact::class));
        self::assertInstanceOf(ComposerPackage::class, $context->fact(ComposerJsonFact::class)->packages()->get('foobar/barfoo'));
        self::assertFalse($context->fact(ComposerJsonFact::class)->packages()->get('foobar/barfoo')->dev());
    }

    public function testLeavesFactFromExisting(): void
    {
        $this->filesystem()->putContents('composer.json', json_encode([
            'require' => [
                'foobar/barfoo' => '1',
            ],
            'require-dev' => [
                'barfoo/foobar' => '2',
            ],
        ]));
        $context = $this->runTask(new ComposerTask(
        ));

        $fact = $context->fact(ComposerJsonFact::class);
        self::assertInstanceOf(ComposerJsonFact::class, $fact);
        self::assertFalse($context->fact(ComposerJsonFact::class)->packages()->get('foobar/barfoo')->dev());
        self::assertTrue($context->fact(ComposerJsonFact::class)->packages()->get('barfoo/foobar')->dev());
    }

    public function testCreatesComposerJsonIfItDoesNotExist(): void
    {
        $this->runTask(new ComposerTask(
            require: [
                'foobar/barfoo' => '^1.0',
            ]
        ));

        self::assertEquals(<<<'EOT'
{
    "require": {
        "foobar/barfoo": "^1.0"
    }
}
EOT
        , $this->filesystem()->getContents('composer.json'));
    }

    /**
     * @dataProvider provideUpdatesComposer
     */
    public function testUpdatesComposer(ComposerTask $composerTask, array $expectedCommands): void
    {
        $this->createComposerJson();
        foreach ($expectedCommands as $expectedCommand) {
            $this->processRunner()->expect(ProcessResult::ok($expectedCommand, '/'));
        }
        $this->runTask($composerTask);

        $this->assertExpectedProcessesRan();
    }

    public function provideUpdatesComposer(): array
    {
        return [
            'require' => [
                new ComposerTask(
                    require: [
                        'baz/boo' => '^1.0',
                    ],
                    composerBin: 'composer',
                ),
                ['php3 composer require baz/boo:^1.0 --no-update --no-scripts'],
            ],
            'require with update' => [
                new ComposerTask(
                    require: [
                        'baz/boo' => '^1.0',
                    ],
                    composerBin: 'composer',
                    update: true
                ),
                [
                    'php3 composer require baz/boo:^1.0 --no-update --no-scripts',
                    'php3 composer update baz/boo --no-scripts',
                ]
            ],
            'require --dev' => [
                new ComposerTask(
                    requireDev: [
                        'baz/boo' => '^1.0',
                    ],
                    dev: true,
                    composerBin: 'composer',
                ),
                ['php3 composer require baz/boo:^1.0 --dev --no-update --no-scripts',]
            ],
            'remove' => [
                new ComposerTask(
                    remove: [
                        'foobar/barfoo',
                        'barfoo/foobar',
                    ],
                    composerBin: 'composer',
                ),
                ['php3 composer remove foobar/barfoo barfoo/foobar --no-update --no-scripts']
            ],
            'both require and requireDev' => [
                new ComposerTask(
                    composerBin: 'composer',
                    require: [
                        'baz/boo' => '^1.0',
                    ],
                    requireDev: [
                        'baz/baz' => '^1.0',
                    ],
                    update: true
                ),
                [
                    'php3 composer require baz/boo:^1.0 --no-update --no-scripts',
                    'php3 composer require baz/baz:^1.0 --dev --no-update --no-scripts',
                    'php3 composer update baz/boo baz/baz --no-scripts',
                ]
            ],
        ];
    }

    /**
     * @dataProvider provideSatisfactory
     */
    public function testSatisfactoryIgnoresSatisfiedConstraints(array $existing, ComposerTask $composerTask, array $expectedCommands, ?int $warnings = null): void
    {
        $this->filesystem()->putContents('composer.json', json_encode($existing));
        foreach ($expectedCommands as $expectedCommand) {
            $this->processRunner()->expect(ProcessResult::ok($expectedCommand, '/'));
        }
        $this->runTask($composerTask);

        $this->assertExpectedProcessesRan();
        if (null !== $warnings) {
            self::assertCount($warnings, $this->reportManager()->reports()->warns());
        }
    }

    public function provideSatisfactory(): array
    {
        return [
            'existing constraint convers lower constriant' => [
                [
                    'require' => [
                        'baz/boo' => '^1.0',
                    ]
                ],
                new ComposerTask(
                    satisfactory: true,
                    require: [
                        'baz/boo' => '^1.2',
                    ],
                ),
                [
                ]
            ],
            'existing constraint same' => [
                [
                    'require' => [
                        'baz/boo' => '^1.0',
                    ]
                ],
                new ComposerTask(
                    satisfactory: true,
                    require: [
                        'baz/boo' => '^1.0',
                    ],
                ),
                [
                ]
            ],
            'existing constraint lower' => [
                [
                    'require' => [
                        'baz/boo' => '^0.9',
                    ]
                ],
                new ComposerTask(
                    satisfactory: true,
                    require: [
                        'baz/boo' => '^1.0',
                    ],
                ),
                [
                    'php3 /usr/local/bin/composer require baz/boo:^1.0 --no-update --no-scripts'
                ]
            ],
            'warning if existing constraint higher' => [
                [
                    'require' => [
                        'baz/boo' => '^2.1',
                    ]
                ],
                new ComposerTask(
                    satisfactory: true,
                    require: [
                        'baz/boo' => '^1.0',
                    ],
                ),
                [
                ],
                1
            ]
        ];
    }
    public function testUpdate(): void
    {
        $this->processRunner()->expect(ProcessResult::ok('php3 composer update --no-scripts', '/'));
        $this->runTask(new ComposerTask(
            update: true,
            composerBin: 'composer',
        ));
        self::assertCount(0, $this->processRunner()->remainingExpectations());
    }

    public function testUpdateRequired(): void
    {
        $this->processRunner()->expect(ProcessResult::ok('php3 composer update foobar/barfoo --no-scripts', '/'));
        $this->runTask(new ComposerTask(
            require: [
                'foobar/barfoo' => '^1.0',
            ],
            update: true,
            composerBin: 'composer',
        ));
        self::assertCount(0, $this->processRunner()->remainingExpectations());
    }

    public function testSkipRequireIfVersionConstraintIsTheSame(): void
    {
        $this->filesystem()->putContents('composer.json', '{"require":{"foobar/barfoo":"^1.0"}}');

        $this->runTask(new ComposerTask(
            require: [
                'foobar/barfoo' => '^1.0',
            ],
        ));
        self::assertCount(0, $this->processRunner()->remainingExpectations());
    }

    public function testDoNotSkipRequireIfVersionConstraintDiffersAtAll(): void
    {
        $this->filesystem()->putContents('composer.json', '{"require":{"foobar/barfoo":"^1.0"}}');
        $this->processRunner()->expect(ProcessResult::ok('php3 composer require foobar/barfoo:"^1.0||^2.0" --no-update --no-scripts', '/'));

        $this->runTask(new ComposerTask(
            composerBin: 'composer',
            require: [
                'foobar/barfoo' => '^1.0||^2.0',
            ],
        ));
        self::assertCount(0, $this->processRunner()->remainingExpectations());
    }

    public function testRequireOnlyOnIntersectionOfPackages(): void
    {
        $this->filesystem()->putContents('composer.json', json_encode([
            'require' => [
                'foobar/barfoo' => '^1.0'
            ],
            'require-dev' => [
                'barfoo/foobar' => '^2.0'
            ],
        ]));
        $this->processRunner()->expect(ProcessResult::ok('php3 composer require foobar/barfoo:"^2.0" --no-update --no-scripts', '/'));
        $this->processRunner()->expect(ProcessResult::ok('php3 composer require barfoo/foobar:"^3.0" --dev --no-update --no-scripts', '/'));

        $this->runTask(new ComposerTask(
            composerBin: 'composer',
            require: [
                'foobar/barfoo' => '^2.0',
                'barfoo/bazbaz' => '^3.0',
            ],
            requireDev: [
                'barfoo/foobar' => '^3.0',
            ],
            intersection: true
        ));
        self::assertCount(0, $this->processRunner()->remainingExpectations());
    }

    public function testUpdateWithAllDependencies(): void
    {
        $this->processRunner()->expect(ProcessResult::ok('php3 composer update foobar/barfoo --with-all-dependencies --no-scripts', '/'));

        $this->runTask(new ComposerTask(
            composerBin: 'composer',
            require: [
                'foobar/barfoo' => '^2.0',
            ],
            update: true,
            withAllDependencies: true
        ));
        self::assertCount(0, $this->processRunner()->remainingExpectations());
    }

    public function testWithScripts(): void
    {
        $this->processRunner()->expect(ProcessResult::ok('php3 composer update foobar/barfoo', '/'));

        $this->runTask(new ComposerTask(
            composerBin: 'composer',
            require: [
                'foobar/barfoo' => '^2.0',
            ],
            update: true,
            runScripts: true
        ));
        self::assertCount(0, $this->processRunner()->remainingExpectations());
    }

    public function testIntersectionRespectsDevStatus(): void
    {
        $this->filesystem()->putContents('composer.json', json_encode([
            'require' => [
                'foobar/barfoo' => '^1.0'
            ],
            'require-dev' => [
                'barfoo/foobar' => '^2.0'
            ],
        ]));

        $this->runTask(new ComposerTask(
            composerBin: 'composer',
            require: [
                'barfoo/foobar' => '^3.0',
            ],
            intersection: true
        ));
        self::assertCount(0, $this->processRunner()->remainingExpectations());
    }

    public function testFailure(): void
    {
        $this->expectException(ProcessFailure::class);
        $this->processRunner()->expect(ProcessResult::fail('php3 compoaaser update --no-scripts', '/'));

        $this->runTask(new ComposerTask(
            update: true,
            composerBin: 'compoaaser',
        ));

        self::assertCount(0, $this->processRunner()->remainingExpectations());
    }

    private function createComposerJson(): void
    {
        $this->filesystem()->putContents('composer.json', '{}');
    }
}
