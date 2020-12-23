<?php

namespace Maestro\Tests\Unit\Composer\Task;

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

    public function testUpdatesComposer(): void
    {
        $this->createComposerJson();
        $this->processRunner()->expect(ProcessResult::ok('php3 composer require baz/boo:^1.0', '/'));

        $this->runTask(new ComposerTask(
            require: [
                'baz/boo' => '^1.0',
            ],
            composerBin: 'composer',
        ));

        self::assertCount(0, $this->processRunner()->remainingExpectations());
    }

    public function testRemoves(): void
    {
        $this->createComposerJson();
        $this->processRunner()->expect(ProcessResult::ok('php3 composer remove foobar/barfoo barfoo/foobar', '/'));
        $this->runTask(new ComposerTask(
            remove: [
                'foobar/barfoo',
                'barfoo/foobar',
            ],
            composerBin: 'composer',
        ));
        self::assertCount(0, $this->processRunner()->remainingExpectations());
    }

    public function testRequireDev(): void
    {
        $this->runTask(new ComposerTask(
            dev: true,
            require: [
                'foobar/barfoo' => '^1.0',
            ]
        ));

        self::assertEquals(<<<'EOT'
{
    "require-dev": {
        "foobar/barfoo": "^1.0"
    }
}
EOT
        , $this->filesystem()->getContents('composer.json'));
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
