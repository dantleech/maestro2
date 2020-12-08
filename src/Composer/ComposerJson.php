<?php

namespace Maestro2\Composer;

final class ComposerJson
{
    public static function fromProjectRoot(string $string): self
    {
        return new self();
    }

    public function autoloadPaths(): array
    {
        return [];
    }
}
