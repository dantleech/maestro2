<?php

namespace Maestro\Composer;

use Stringable;

class ComposerVersion implements Stringable
{
    private string $version;

    public function __construct(string $version)
    {
        $this->version = $version;
    }

    public function __toString(): string
    {
        return $this->version;
    }
}
