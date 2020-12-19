<?php

namespace Maestro2\Core\Process;

use Amp\Promise;
use Amp\Success;
use Maestro2\Core\Exception\RuntimeException as Maestro2RuntimeException;
use Maestro2\Core\Process\Exception\ProcessFailure;
use function Amp\call;

class TestProcessRunner implements ProcessRunner
{
    /**
     * @var list<ProcessResult>
     */
    private array $expectations = [];

    /**
     * @return list<ProcessResult>
     */
    public function remainingExpectations(): array
    {
        return $this->expectations;
    }

    public function run(array $args, ?string $cwd = null): Promise
    {
        foreach ($this->expectations as $index => $result) {
            assert($result instanceof ProcessResult);
            if ($result->cmd() === $args) {
                unset($this->expectations[$index]);
                return new Success($result);
            }
        }

        throw new Maestro2RuntimeException(sprintf(
            'Could not find test expectation for process "%s"',
            join(' ', $args)
        ));
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

    public function expect(ProcessResult $processResult): void
    {
        $this->expectations[] = $processResult;
    }
}
