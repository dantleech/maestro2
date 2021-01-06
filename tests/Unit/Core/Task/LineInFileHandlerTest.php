<?php

namespace Maestro\Tests\Unit\Core\Task;

use Maestro\Core\Task\LineInFileTask;

class LineInFileHandlerTest extends HandlerTestCase
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

        $this->runTask(new LineInFileTask(
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

    public function testAddWhenNotExisting(): void
    {
        $this->filesystem()->putContents(
            'text',
            <<<'EOT'
Line one
Line two
Line three
EOT
        );

        $this->runTask(new LineInFileTask(
            path: 'text',
            regexp: "{four}",
            line: "Line four",
            append: true
        ));

        self::assertEquals(<<<'EOT'
Line one
Line two
Line three
Line four
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

        $this->runTask(new LineInFileTask(
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
