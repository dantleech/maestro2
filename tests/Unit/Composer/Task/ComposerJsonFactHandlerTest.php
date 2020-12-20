<?php

namespace Maestro\Tests\Unit\Composer\Task;

use Maestro\Composer\Fact\ComposerJsonFact;
use Maestro\Composer\Task\ComposerJsonFactTask;
use Maestro\Tests\Unit\Core\Task\HandlerTestCase;

class ComposerJsonFactHandlerTest extends HandlerTestCase
{
    public function testProvidesComposerJsonFact(): void
    {
        $this->filesystem()->putContents(
            'composer.json',
            <<<'EOT'
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
