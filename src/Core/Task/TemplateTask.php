<?php

namespace Maestro2\Core\Task;

use Stringable;

final class TemplateTask implements Task, Stringable
{
    public function __construct(
        private string $template,
        private string $target,
        private array $vars = [],
        private int $mode = 0644,
        private bool $overwrite = false
    ) {
    }

    public function target(): string
    {
        return $this->target;
    }

    public function template(): string
    {
        return $this->template;
    }

    public function vars(): array
    {
        return $this->vars;
    }

    public function mode(): int
    {
        return $this->mode;
    }

    public function overwrite(): bool
    {
        return $this->overwrite;
    }


    public function __toString(): string
    {
        return sprintf(
            'Applied "%s" to "%s"',
            $this->template,
            $this->target
        );
    }
}
