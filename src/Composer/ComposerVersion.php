<?php

namespace Maestro\Composer;

use Composer\Semver\Comparator;
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

    public function version(): string
    {
        return $this->version;
    }

    public function greaterThan(string $version): bool
    {
        return Comparator::greaterThan($this->version, $version);
    }

    public function greaterThanOrEqualTo(string $version): bool
    {
        return Comparator::greaterThanOrEqualTo($this->version, $version);
    }

    public function lessThan(string $version): bool
    {
        return Comparator::lessThan($this->version, $version);
    }

    public function lessThanOrEqualTo(string $version): bool
    {
        return Comparator::lessThanOrEqualTo($this->version, $version);
    }

    public function equalTo(string $version): bool
    {
        return Comparator::equalTo($this->version, $version);
    }

    public function notEqualTo(string $version): bool
    {
        return Comparator::notEqualTo($this->version, $version);
    }
}
