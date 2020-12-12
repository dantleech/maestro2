<?php

namespace Maestro2\Tests\Util;

use Symfony\Component\Process\Process;

final class ProcessUtil
{
    public static function mustRun(string $cwd, string $cmd): void
    {
        (Process::fromShellCommandline($cmd, $cwd))->mustRun();
    }
}
