<?php

namespace Maestro2\Core\Process;

use Maestro2\Core\Process\Exception\ProcessFailure;
use Maestro2\Core\Task\ProcessTask;

class ProcessResult
{
    public function __construct(private int $exitCode, private string $stdOut, private string $stdErr)
    {
    }

    public static function ok(string $stdOut = '', string $stdErr = ''): self
    {
        return new self(0, $stdOut, $stdErr);
    }

    public static function new(int $exitCode, string $stdOut = '', string $stdErr = ''): self
    {
        return new self($exitCode, $stdOut, $stdErr);
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
}
