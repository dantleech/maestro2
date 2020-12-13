<?php

namespace Maestro2\Tests\Unit\Composer\Task;

use Maestro2\Composer\ComposerJson;
use Maestro2\Composer\Fact\ComposerJsonFact;
use Maestro2\Composer\Task\ComposerJsonFactHandler;
use Maestro2\Composer\Task\ComposerJsonFactTask;
use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\Handler;
use Maestro2\Tests\Unit\Core\Task\HandlerTestCase;
use PHPUnit\Framework\TestCase;

class ComposerJsonFactHandlerTest extends HandlerTestCase
{
    protected function createHandler(): Handler
    {
        return new ComposerJsonFactHandler();
    }

    protected function defaultContext(): Context
    {
        return Context::withFacts(
            new CwdFact($this->workspace()->path())
        );
    }

    public function testProvidesComposerJsonFact(): void
    {
        $this->workspace()->put('composer.json', <<<'EOT'
{
    "name": "foobar/barfoo",
    "autoload": {
        "psr-4": {
            "Foobar\\": "foo/"
        }
    }
}
EOT
        );
        $context = $this->runTask(new ComposerJsonFactTask());

        self::assertEquals(['foo/'], $context->fact(ComposerJsonFact::class)->autoloadPaths());
    }
}
