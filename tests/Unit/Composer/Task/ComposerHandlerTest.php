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
        $this->filesystem()->putContents(
            'composer.json',
            <<<'EOT'
{
    "require": {
        "foobar/barfoo": "^1.0"
    }
}
EOT
        );

        $this->runTask(new ComposerTask(
            require: [
                'baz/boo' => '^1.0',
            ]
        ));

        self::assertEquals(<<<'EOT'
{
    "require": {
        "foobar/barfoo": "^1.0",
        "baz/boo": "^1.0"
    }
}
EOT
        , $this->filesystem()->getContents('composer.json'));
    }

    public function testRemoves(): void
    {
        $this->filesystem()->putContents(
            'composer.json',
            <<<'EOT'
{
    "require": {
        "foobar/barfoo": "^1.0",
        "baz/boo": "^1.0"
    }
}
EOT
        );

        $this->runTask(new ComposerTask(
            remove: [
                'foobar/barfoo'
            ]
        ));

        self::assertEquals(<<<'EOT'
{
    "require": {
        "baz/boo": "^1.0"
    }
}
EOT
        , $this->filesystem()->getContents('composer.json'));
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
}
