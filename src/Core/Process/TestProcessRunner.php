<?php

namespace Maestro2\Core\Process;

use Amp\Promise;
use Amp\Success;
use RuntimeException;

class TestProcessRunner implements ProcessRunner
{
    private array $ran = [];

    public function pop(): TestProcess
    {
        if (!$process = array_shift($this->ran)) {
            throw new RuntimeException(
                'No test processes were invoked'
            );
        }

        return $process;
    }

    public function run(array $args, ?string $cwd = null): Promise
    {
        $this->ran[] = new TestProcess($args, $cwd);
        return new Success();
    }

    public function mustRun(array $args, ?string $cwd = null): Promise
    {
        return $this->run($args, $cwd);
    }
}
