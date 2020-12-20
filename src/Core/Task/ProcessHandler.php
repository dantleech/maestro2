<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Closure;
use Maestro\Core\Fact\CwdFact;
use Maestro\Core\Fact\GroupFact;
use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\Process\Exception\ProcessFailure;
use Maestro\Core\Process\ProcessResult;
use Maestro\Core\Process\ProcessRunner;
use Maestro\Core\Report\Publisher\NullPublisher;
use Maestro\Core\Report\Report;
use Maestro\Core\Report\ReportPublisher;
use Maestro\Core\Task\Exception\TaskError;
use function Amp\call;

class ProcessHandler implements Handler
{
    private ReportPublisher $reportPublisher;

    public function __construct(
        private Filesystem $filesystem,
        private ProcessRunner $processRunner,
        ?ReportPublisher $reportPublisher = null
    ) {
        $this->reportPublisher = $reportPublisher ?: new NullPublisher();
    }

    public function taskFqn(): string
    {
        return ProcessTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof ProcessTask);
        return call(function (string $cwd, string $group) use ($task, $context) {
            $result = yield $this->processRunner->run(
                $task->cmd(),
                $this->filesystem->localPath($cwd)
            );
            assert($result instanceof ProcessResult);

            if (false === $result->isOk()) {
                $this->handleFailure($task, $result, $group);
            }

            return (static function (?Closure $after, ProcessResult $result, Context $context): Context {
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
            })($task->after(), $result, $context)->withResult($result);
        }, $context->factOrNull(CwdFact::class)?->cwd() ?: '/', $context->factOrNull(GroupFact::class)?->group() ?: 'process');
    }

    private function formatArgs(ProcessTask $task): string
    {
        return implode(' ', array_map('escapeshellarg', $task->cmd()));
    }

    private function handleFailure(ProcessTask $task, ProcessResult $result, string $group): void
    {
        if (false === $task->allowFailure()) {
            throw (function (ProcessFailure $failure) {
                return new TaskError($failure->getMessage(), 0, $failure);
            })(ProcessFailure::fromResult($result, $task->cmd()));
        }

        $this->reportPublisher->publish($group, Report::warn(sprintf(
            'Tolerated process failure: "%s" failed with "%s"',
            $this->formatArgs($task),
            $result->exitCode()
        )));
    }
}
