<?php

namespace Maestro2\Core\Process;

use Amp\Process\Process;
use Amp\Process\ProcessInputStream;

class ProcessResult
{
    public function __construct(private int $exitCode, private string $stdOut, private string $stdErr)
    {
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
