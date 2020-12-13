<?php

namespace Maestro2\Core\Process;

use Amp\Promise;
use Amp\Success;
use Maestro2\Core\Process\Exception\ProcessFailure;
use RuntimeException;
use function Amp\call;

class TestProcessRunner implements ProcessRunner
{
    private array $ran = [];
    private array $results = [];

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
        return new Success($this->shiftResult());
    }

    public function mustRun(array $args, ?string $cwd = null): Promise
    {
        return call(function () use ($args, $cwd) {
            $result = yield $this->run($args, $cwd);
            if (0 !== $result->exitCode()) {
                throw ProcessFailure::fromResult($result, $args);
            }

            return $result;
        });
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
