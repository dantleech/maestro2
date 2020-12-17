<?php

namespace Maestro2\Core\Process;

class ProcessResult
{
    public function __construct(
        private int $exitCode,
        private string $stdOut,
        private string $stdErr,
        private array $args,
        private string $cwd
    )
    {
    }

    public static function ok(array $args, string $cwd, string $stdOut = '', string $stdErr = ''): self
    {
        return new self(0 , $stdOut, $stdErr, $args, $cwd);
    }

    public static function new(array $args, string $cwd, int $exitCode, string $stdOut = '', string $stdErr = ''): self
    {
        return new self($exitCode, $stdOut, $stdErr, $args, $cwd);
    }

    public function exitCode(): int
    {
        return $this->exitCode;
    }

    public function isOk(): bool
    {
        return $this->exitCode === 0;
    }

    public function stdOut(): string
    {
        return $this->stdOut;
    }

    public function stdErr(): string
    {
        return $this->stdErr;
    }

    public function cwd(): string
    {
        return $this->cwd;
    }
}
