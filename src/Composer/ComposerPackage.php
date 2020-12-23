<?php

namespace Maestro\Composer;

class ComposerPackage
{
    public function __construct(private string $name, private string $version)
    {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function version(): string
    {
        return $this->version;
    }
}
