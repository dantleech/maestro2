<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Task\ReplaceLineTask;

class ReplaceLineHandlerTest extends HandlerTestCase
{
    public function testReplacesLine(): void
    {
        $this->filesystem()->putContents(
            'text',
            <<<'EOT'
Line one
Line two
Line three
EOT
        );

        $this->runTask(new ReplaceLineTask(
            path: 'text',
            regexp: "{two}",
            line: "Line four",
        ));

        self::assertEquals(<<<'EOT'
Line one
Line four
Line three
EOT
        , $this->filesystem()->getContents('text'));
    }

    public function testDoesNotModifyWhenNoMatches(): void
    {
        $this->filesystem()->putContents(
            'text',
            <<<'EOT'
Line one
Line two
Line three
EOT
        );

        $this->runTask(new ReplaceLineTask(
            path: 'text',
            regexp: "{sity}",
            line: "Line four",
        ));

        self::assertEquals(<<<'EOT'
Line one
Line two
Line three
EOT
        , $this->filesystem()->getContents('text'));
    }
}
