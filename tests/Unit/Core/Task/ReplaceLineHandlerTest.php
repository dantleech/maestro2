<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Report\Publisher\NullPublisher;
use Maestro2\Core\Task\Handler;
use Maestro2\Core\Task\ReplaceLineHandler;
use Maestro2\Core\Task\ReplaceLineTask;

class ReplaceLineHandlerTest extends HandlerTestCase
{
    protected function createHandler(): Handler
    {
        return new ReplaceLineHandler(new NullPublisher());
    }

    public function testReplacesLine(): void
    {
        $this->workspace()->put(
            'text',
            <<<'EOT'
Line one
Line two
Line three
EOT
        );

        $this->runTask(new ReplaceLineTask(
            path: $this->workspace()->path('text'),
            regexp: "{two}",
            line: "Line four",
        ));

        self::assertEquals(<<<'EOT'
Line one
Line four
Line three
EOT
        , $this->workspace()->getContents('text'));
    }

    public function testDoesNotModifyWhenNoMatches(): void
    {
        $this->workspace()->put(
            'text',
            <<<'EOT'
Line one
Line two
Line three
EOT
        );

        $this->runTask(new ReplaceLineTask(
            path: $this->workspace()->path('text'),
            regexp: "{sity}",
            line: "Line four",
        ));

        self::assertEquals(<<<'EOT'
Line one
Line two
Line three
EOT
        , $this->workspace()->getContents('text'));
    }
}
