<?php

namespace Maestro2\Core\Process;

use Amp\Promise;

interface ProcessRunner
{
    /**
     * @return Promise<ProcessResult>
     */
    public function run(array $args, ?string $cwd = null): Promise;

    public function mustRun(array $args, ?string $cwd = null): Promise;
}
