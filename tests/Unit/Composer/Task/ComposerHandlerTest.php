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

    public function testLeavesFact(): void
    {
        $context = $this->runTask(new ComposerTask(
            require: [
                'foobar/barfoo' => '^1.0',
            ]
        ));

        self::assertInstanceOf(ComposerJsonFact::class, $context->fact(ComposerJsonFact::class));
        self::assertInstanceOf(ComposerPackage::class, $context->fact(ComposerJsonFact::class)->packages()->get('foobar/barfoo'));
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
    public function testUpdatesComposer(ComposerTask $composerTask, string $expectedCommand): void
    {
        $this->createComposerJson();
        $this->processRunner()->expect(ProcessResult::ok($expectedCommand, '/'));
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
                'php3 composer require baz/boo:^1.0 --no-update'
            ],
            'require with update' => [
                new ComposerTask(
                    require: [
                        'baz/boo' => '^1.0',
                    ],
                    update: true,
                    composerBin: 'composer',
                ),
                'php3 composer require baz/boo:^1.0'
            ],
            'require --dev' => [
                new ComposerTask(
                    require: [
                        'baz/boo' => '^1.0',
                    ],
                    dev: true,
                    composerBin: 'composer',
                ),
                'php3 composer require baz/boo:^1.0 --dev --no-update',
            ],
            'remove' => [
                new ComposerTask(
                    remove: [
                        'foobar/barfoo',
                        'barfoo/foobar',
                    ],
                    composerBin: 'composer',
                ),
                'php3 composer remove foobar/barfoo barfoo/foobar --no-update'
            ],
            'remove --dev' => [
                new ComposerTask(
                    remove: [
                        'foobar/barfoo',
                        'barfoo/foobar',
                    ],
                    dev: true,
                    composerBin: 'composer',
                ),
                'php3 composer remove foobar/barfoo barfoo/foobar --dev --no-update'
            ],
        ];
    }

    public function testUpdate(): void
    {
        $this->processRunner()->expect(ProcessResult::ok('php3 composer update', '/'));
        $this->runTask(new ComposerTask(
            update: true,
            composerBin: 'composer',
        ));
        self::assertCount(0, $this->processRunner()->remainingExpectations());
    }

    public function testSkipRequireIfVersionConstraintAlreadSatisfied(): void
    {
        $this->filesystem()->putContents('composer.json', '{"require":{"foobar/barfoo":"^1.0"}}');

        $this->runTask(new ComposerTask(
            require: [
                'foobar/barfoo' => '^1.0',
            ],
        ));
        self::assertCount(1, $this->reportManager()->reports()->infos());
    }

    public function testFailure(): void
    {
        $this->expectException(ProcessFailure::class);
        $this->processRunner()->expect(ProcessResult::fail('php3 compoaaser update', '/'));

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
