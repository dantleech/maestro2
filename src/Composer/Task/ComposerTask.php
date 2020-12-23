<?php

namespace Maestro\Composer\Task;

use Maestro\Core\Task\Task;
use Stringable;

class ComposerTask implements Task, Stringable
{
    public function __construct(
        private array $require = [],
        private array $remove = [],
        private bool $update = false,
        private bool $dev = false,
        private ?string $composerBin = null,
    ) {
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

    public function composerBin(): ?string
    {
        return $this->composerBin;
    }

    public function __toString(): string
    {
        return sprintf(
            'Updating composer: dev %s, require [%s], remove: [%s], update %s',
            $this->dev ? 'yes' : 'no',
            implode(', ', array_map(
                fn (string $name, string $version) => sprintf('%s:%s', $name, $version),
                array_keys($this->require),
                array_values($this->require)
            )),
            implode(', ', array_keys($this->remove)),
            $this->update ? 'yes' : 'no'
        );
    }
}
