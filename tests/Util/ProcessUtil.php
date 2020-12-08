<?php

namespace Maestro2\Tests\Util;

use Maestro2\Core\Process\AmpProcessRunner;
use Maestro2\Core\Process\ProcessRunner;
use RuntimeException;
use Symfony\Component\Process\Process;
use function Amp\Promise\wait;
use function Amp\call;

final class ProcessUtil
{
    public static function mustRun(string $cwd, string $cmd): void
    {
        (Process::fromShellCommandline($cmd, $cwd))->mustRun();
    }
}
