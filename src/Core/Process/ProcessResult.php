<?php

namespace Maestro2\Core\Process;

use Amp\Process\Process;
use Amp\Process\ProcessInputStream;

class ProcessResult
{
    public function __construct(private int $exitCode)
    {
    }

    public function exitCode(): int
    {
        return $this->exitCode;
    }
}
