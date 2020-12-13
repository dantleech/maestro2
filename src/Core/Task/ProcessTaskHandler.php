<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Closure;
use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Process\Exception\ProcessFailure;
use Maestro2\Core\Process\ProcessResult;
use Maestro2\Core\Process\ProcessRunner;
use Maestro2\Core\Task\Exception\TaskError;
use function Amp\call;

class ProcessTaskHandler implements Handler
{
    public function __construct(
        private ProcessRunner $processRunner
    ) {
    }

    public function taskFqn(): string
    {
        return ProcessTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof ProcessTask);
        return call(function (string $cwd) use ($task, $context) {
            $result = yield $this->processRunner->run(
                $task->args(),
                $cwd
            );
            assert($result instanceof ProcessResult);

            if (false === $task->allowFailure() && false === $result->isOk()) {
                throw (function (ProcessFailure $failure) {
                    return new TaskError($failure->getMessage(), 0, $failure);
                })(ProcessFailure::fromResult($result, $task->args()));
            }

            return (static function (?Closure $after, ProcessResult $result, Context $context): Context  {
                if (null === $after) {
                    return $context;
                }

                $context = $after($result, $context);

                /**
                 * @psalm-suppress RedundantCondition 
                 * @psalm-suppress TypeDoesNotContainType
                 */
                if (!$context instanceof Context) {
                    throw new TaskError(sprintf(
                        'Process after-closure must return the Context (which is passed as the 2nd argument to the closure, got "%s"',
                        is_object($context) ? $context::class : gettype($context)
                    ));
                }

                return $context;
            })($task->after(), $result, $context);
        }, $task->cwd() ?: $context->fact(CwdFact::class)->cwd());
    }

    private function formatArgs(ProcessTask $task): string
    {
        return implode(' ', array_map('escapeshellarg', $task->args()));
    }
}
