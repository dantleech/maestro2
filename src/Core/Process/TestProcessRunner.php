<?php

namespace Maestro2\Core\Process;

use Amp\Promise;
use Amp\Success;
use RuntimeException;

class TestProcessRunner implements ProcessRunner
{
    private array $ran = [];
    private array $results = [];

    public function pop(): TestProcess
    {
        if (!$process = array_shift($this->ran)) {
            throw new RuntimeException(
                'No more test processes on stack'
            );
        }

        return $process;
    }

    public function run(array $args, ?string $cwd = null): Promise
    {
        $this->ran[] = new TestProcess($args, $cwd);
        return new Success($this->shiftResult());
    }

    public function mustRun(array $args, ?string $cwd = null): Promise
    {
        return $this->run($args, $cwd);
    }

    public function push(ProcessResult $processResult): void
    {
        $this->results[] = $processResult;
    }

    private function shiftResult(): ProcessResult
    {
        if ([] === $this->results) {
            throw new RuntimeException(
                'No more results left in test process runner'
            );
        }

        return array_shift($this->results);
    }
}
