<?php

namespace Maestro2\Core\Task;
use Stringable;

class ComposerTask implements Task, Stringable
{
    public function __construct(
        private ?string $path = null,
        private array $require = [],
        private array $remove = [],
        private bool $update = false,
        private ?string $group = null,
        private bool $dev = false,
        private string $phpBin = PHP_BINARY,
        private ?string $composerBin = null,
    ) {
    }

    public function group(): ?string
    {
        return $this->group;
    }

    public function path(): ?string
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

    public function __toString(): string
    {
        return sprintf(
            'Updating composer: dev %s, require %s, remove: %s, update %s',
            $this->dev ? 'yes' : 'no',
            implode(', ', array_keys($this->require)),
            implode(', ', array_keys($this->remove)),
            $this->update ? 'yes' : 'no'
        );

    }
}
