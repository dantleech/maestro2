<?php

namespace Maestro\Composer;

class ComposerPackage
{
    public function __construct(private string $name, private string $version, private bool $dev)
    {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function version(): ComposerVersion
    {
        return new ComposerVersion($this->version);
    }

    public function dev(): bool
    {
        return $this->dev;
    }
}
