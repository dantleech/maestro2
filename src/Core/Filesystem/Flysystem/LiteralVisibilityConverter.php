<?php

namespace Maestro2\Core\Filesystem\Flysystem;

use League\Flysystem\UnixVisibility\VisibilityConverter;
use RuntimeException;

class LiteralVisibilityConverter implements VisibilityConverter
{
    public function forFile(string $visibility): int
    {
        return (int)$visibility;
    }

    public function forDirectory(string $visibility): int
    {
        return (int)$visibility;
    }

    public function inverseForFile(int $visibility): string
    {
        throw new RuntimeException('Not implemented');
    }

    public function inverseForDirectory(int $visibility): string
    {
        throw new RuntimeException('Not implemented');
    }

    public function defaultForDirectories(): int
    {
        return 0700;
    }
}
