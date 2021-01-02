<?php

namespace Maestro\Tests\Unit\Markdown\Task;

use Maestro\Markdown\Task\MarkdownSectionTask;
use Maestro\Tests\Unit\Core\Task\HandlerTestCase;
use PHPUnit\Framework\TestCase;

class MarkdownSectionHandlerTest extends HandlerTestCase
{
    public function testCreateNewFile(): void
    {
        $context = $this->runTask(new MarkdownSectionTask(
            path: 'README.md',
            header: "## Contributing",
            content: <<<EOT
## Contributing

This is my new content

EOT
        ));

        self::assertEquals(<<<EOT
## Contributing

This is my new content

EOT
        , $this->filesystem()->getContents('README.md'));
    }

    public function testReplaceSection(): void
    {
        $this->filesystem()->putContents('README.md', <<<EOT
# Hello

This is a README

## Install

Install it with your hands.

## Contributing

Yes

## Suport

No
EOT
        );

        $context = $this->runTask(new MarkdownSectionTask(
            path: 'README.md',
            header: "## Contributing",
            content: <<<EOT
## Contributing

This is my new content

EOT
        ));

        self::assertEquals(<<<EOT
# Hello

This is a README

## Install

Install it with your hands.

## Contributing

This is my new content

## Suport

No
EOT
        , $this->filesystem()->getContents('README.md'));
    }
}
