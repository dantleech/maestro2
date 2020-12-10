<?php

namespace Maestro2\PhpStan\Task;

use Maestro2\Core\Task\Task;

class PhpStanTask implements Task
{
    public function __construct(
        private string $repoPath,
        private string $phpstanVersion = '*',
        private array $paths,
        private int $level = 7,
        private string $phpBin = PHP_BINARY,
    ) {
    }

    public function level(): int
    {
        return $this->level;
    }

    public function paths(): array
    {
        return $this->paths;
    }

    public function phpstanVersion(): string
    {
        return $this->phpstanVersion;
    }

    public function repoPath(): string
    {
        return $this->repoPath;
    }

    public function phpBin(): string
    {
        return $this->phpBin;
    }
}
