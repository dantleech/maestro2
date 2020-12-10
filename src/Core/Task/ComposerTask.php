<?php

namespace Maestro2\Core\Task;

use Maestro2\Core\Task\Task;

class ComposerTask implements Task
{
    public function __construct(
        private string $path,
        private array $require = [],
        private array $remove = [],
        private bool $update = false,
        private string $group = 'composer',
        private bool $dev = false,
        private string $phpBin = PHP_BINARY,
        private ?string $composerBin = null,
    ) {
    }

    public function group(): string
    {
        return $this->group;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function remove(): array
    {
        return $this->remove;
    }

    public function require(): array
    {
        return $this->require;
    }

    public function dev(): bool
    {
        return $this->dev;
    }

    public function update(): bool
    {
        return $this->update;
    }

    public function phpBin(): string
    {
        return $this->phpBin;
    }

    public function composerBin(): ?string
    {
        return $this->composerBin;
    }
}
