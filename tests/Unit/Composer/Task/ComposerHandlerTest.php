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
                ['php3 composer require baz/boo:^1.0 --no-update'],
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
                    'php3 composer require baz/boo:^1.0 --no-update',
                    'php3 composer update baz/boo',
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
                ['php3 composer require baz/boo:^1.0 --dev --no-update',]
            ],
            'remove' => [
                new ComposerTask(
                    remove: [
                        'foobar/barfoo',
                        'barfoo/foobar',
                    ],
                    composerBin: 'composer',
                ),
                ['php3 composer remove foobar/barfoo barfoo/foobar --no-update']
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

    public function testUpdateRequired(): void
    {
        $this->processRunner()->expect(ProcessResult::ok('php3 composer update foobar/barfoo', '/'));
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
        $this->processRunner()->expect(ProcessResult::ok('php3 composer require foobar/barfoo:"^1.0||^2.0" --no-update', '/'));

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
        $this->filesystem()->putContents('composer.json', '{"require":{"foobar/barfoo":"^1.0"}}');
        $this->processRunner()->expect(ProcessResult::ok('php3 composer require foobar/barfoo:"^2.0" --no-update', '/'));

        $this->runTask(new ComposerTask(
            composerBin: 'composer',
            require: [
                'foobar/barfoo' => '^2.0',
                'barfoo/bazbaz' => '^3.0',
            ],
            intersection: true
        ));
        self::assertCount(0, $this->processRunner()->remainingExpectations());
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
