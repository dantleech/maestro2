<?php

namespace Maestro\Tests\Unit\Markdown\Task;

use Maestro\Core\Task\Exception\TaskError;
use Maestro\Markdown\Task\MarkdownSectionTask;
use Maestro\Tests\Unit\Core\Task\HandlerTestCase;

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
        $this->filesystem()->putContents(
            'README.md',
            <<<EOT
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

    public function testReplaceSectionUntilNextEqualHeader(): void
    {
        $this->filesystem()->putContents(
            'README.md',
            <<<EOT
## Contributing

Yes

### Notes on Contribution

#### Studies

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
## Contributing

This is my new content

## Suport

No
EOT
        , $this->filesystem()->getContents('README.md'));
    }

    public function testAppendContentIfNotMatch(): void
    {
        $this->filesystem()->putContents(
            'README.md',
            <<<EOT
## Hello

Yes

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
## Hello

Yes

## Contributing

This is my new content
EOT
        , $this->filesystem()->getContents('README.md'));
    }

    public function testPrependContentIfNotMatch(): void
    {
        $this->filesystem()->putContents(
            'README.md',
            <<<EOT
## Hello

Yes

EOT
        );

        $context = $this->runTask(new MarkdownSectionTask(
            path: 'README.md',
            prepend: true,
            header: "## Contributing",
            content: <<<EOT
## Contributing

This is my new content

EOT
        ));

        self::assertEquals(<<<EOT
## Contributing

This is my new content

## Hello

Yes

EOT
        , $this->filesystem()->getContents('README.md'));
    }

    public function testRendersATemplate(): void
    {
        $this->workspace()->put(
            'templates/contributing.md.twig',
            <<<EOT
Good day
EOT
        );

        $context = $this->runTask(new MarkdownSectionTask(
            path: 'README.md',
            header: "## Contributing",
            template: 'contributing.md.twig',
            vars: [
                'foo' => 'bar',
            ]
        ));

        self::assertEquals(<<<EOT
Good day
EOT
        , $this->filesystem()->getContents('README.md'));
    }

    public function testEmptyContentWhenNeitherTemplateNorContentGiven(): void
    {
        $context = $this->runTask(new MarkdownSectionTask(
            path: 'README.md',
            header: "## Contributing"
        ));

        self::assertEquals(<<<EOT
EOT
        , $this->filesystem()->getContents('README.md'));
    }

    public function testExceptionWhenBothContentAndTemplateProvided(): void
    {
        $this->expectException(TaskError::class);
        $this->expectExceptionMessage('You cannot provide both');
        $context = $this->runTask(new MarkdownSectionTask(
            path: 'README.md',
            header: "## Contributing",
            content: 'foo',
            template: 'bar'
        ));
    }
}
