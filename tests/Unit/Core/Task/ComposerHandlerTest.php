<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Fact\PhpFact;
use Maestro2\Core\Process\Exception\ProcessFailure;
use Maestro2\Core\Process\ProcessResult;
use Maestro2\Core\Task\ComposerHandler;
use Maestro2\Core\Task\ComposerTask;
use Maestro2\Core\Process\TestProcessRunner;
use Maestro2\Core\Queue\TestEnqueuer;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\Handler;
use Maestro2\Core\Task\JsonMergeHandler;
use Maestro2\Core\Task\PhpProcessHandler;
use Maestro2\Core\Task\ProcessHandler;

class ComposerHandlerTest extends HandlerTestCase
{
    private TestProcessRunner $testRunner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testRunner = new TestProcessRunner();
    }

    protected function defaultContext(): Context
    {
        return Context::fromFacts(
            new PhpFact(
                phpBin: 'php3',
            ),
            new CwdFact('/')
        );
    }

    protected function createHandler(): Handler
    {
        $filesystem = $this->filesystem();
        return new ComposerHandler(
            $filesystem,
            TestEnqueuer::fromHandlers([
                new JsonMergeHandler($filesystem),
                new PhpProcessHandler(TestEnqueuer::fromHandlers([
                    new ProcessHandler($filesystem, $this->testRunner),
                ]))
            ]),
        );
    }

    public function testCreatesComposer(): void
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
        , $this->workspace()->getContents('composer.json'));
    }

    public function testUpdatesComposer(): void
    {
        $this->workspace()->put(
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
        , $this->workspace()->getContents('composer.json'));
    }

    public function testRemoves(): void
    {
        $this->workspace()->put(
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
        , $this->workspace()->getContents('composer.json'));
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
        , $this->workspace()->getContents('composer.json'));
    }

    public function testUpdate(): void
    {
        $this->testRunner->expect(ProcessResult::ok('php3 composer update', '/'));
        $this->runTask(new ComposerTask(
            update: true,
            composerBin: 'composer',
        ));
        self::assertCount(0, $this->testRunner->remainingExpectations());
    }

    public function testFailure(): void
    {
        $this->expectException(ProcessFailure::class);
        $this->testRunner->expect(ProcessResult::fail('php3 compoaaser update', '/'));

        $this->runTask(new ComposerTask(
            update: true,
            composerBin: 'compoaaser',
        ));

        self::assertCount(0, $this->testRunner->remainingExpectations());
    }
}
