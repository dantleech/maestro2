<?php

namespace Maestro\Markdown\Task;

use Maestro\Core\Task\Task;

class MarkdownSectionTask implements Task
{
    public function __construct(private string $path, private string $header, private string $content)
    {
    }

    public function path(): string
    {
        return $this->path;
    }

    public function header(): string
    {
        return $this->header;
    }

    public function content(): string
    {
        return $this->content;
    }
}
