<?php

namespace Maestro\Core\Process\Exception;

use Maestro\Core\Exception\RuntimeException;
use Maestro\Core\Process\ProcessResult;

class ProcessFailure extends RuntimeException
{
    public static function fromResult(ProcessResult $result, array $args): self
    {
        throw new self(sprintf(
            '`%s` exited with code "%s": %s %s',
            implode(' ', $args),
            $result->exitCode(),
            $result->stdOut(),
            $result->stdErr()
        ));
    }
}
