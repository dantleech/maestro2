<?php

namespace Maestro\Markdown\Task;

use Maestro\Core\Task\Task;

/**
 * Replace a section in a markdown document corresponding to the given header.
 *
 * ```php
 * new MarkdownSectionTask(
 *     path: 'README.md',
 *     header: '## Contributing',
 *     content: "## Contributing\n\nHello There"
 * );
 * ```
 *
 * If the document does not exist, it will be created.
 */
class MarkdownSectionTask implements Task
{
    /**
     * @param string $path Path to existing or target markdown file
     * @param string $header Header to match
     * @param string $content Content to replace section with
     * @param bool $prepend Prepend a new section instead of appending it.
     */
    public function __construct(
        private string $path,
        private string $header,
        private ?string $content = null,
        private bool $prepend = false,
        private ?string $template = null,
        private array $vars = [],
    ) {
    }

    public function path(): string
    {
        return $this->path;
    }

    public function header(): string
    {
        return $this->header;
    }

    public function content(): ?string
    {
        return $this->content;
    }

    public function prepend(): bool
    {
        return $this->prepend;
    }

    public function template(): ?string
    {
        return $this->template;
    }

    public function vars(): array
    {
        return $this->vars;
    }
}
