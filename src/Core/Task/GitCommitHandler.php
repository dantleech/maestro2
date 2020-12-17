<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Fact\GroupFact;
use Maestro2\Core\Process\ProcessResult;
use Maestro2\Core\Queue\Enqueuer;
use Maestro2\Core\Report\Report;
use Maestro2\Core\Report\ReportPublisher;
use Maestro2\Core\Task\Exception\TaskError;
use function Amp\call;

class GitCommitHandler implements Handler
{
    public function __construct(
        private Enqueuer $enqueuer,
        private ReportPublisher $publisher
    ) {
    }

    public function taskFqn(): string
    {
        return GitCommitTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof GitCommitTask);
        return call(function () use ($task, $context) {
            $result = (yield $this->enqueuer->enqueue(
                TaskContext::create(new ProcessTask(
                    args: ['git', 'rev-parse', '--show-toplevel'],
                    allowFailure: true
                ), $context)
            ))->result();
            assert($result instanceof ProcessResult);

            if (!$result->isOk()) {
                throw new TaskError(sprintf(
                    'Path "%s" is not a git repository',
                    $result->cwd()
                ));
            }

            (function (string $topLevelPath, string $cwd) use ($task) {
                if ($topLevelPath === $cwd) {
                    return;
                }
                throw new TaskError(sprintf(
                    'Path "%s" is not the root of a git repository (root is "%s")',
                    $cwd,
                    $topLevelPath
                ));
            })(trim($result->stdOut()), $result->cwd());

            $result = (yield $this->enqueuer->enqueue(
                TaskContext::create(new ProcessTask(
                    args: array_merge([
                        'git',
                        'ls-files',
                        '-m',
                    ], $task->paths()),
                ), $context)
            ))->result();

            if ($result->stdOut() === '') {
                $this->publisher->publish(
                    $task->group() ?: $context->fact(GroupFact::class)->group(),
                    Report::warn(sprintf('Git commit "%s": no files modiied', $task->message()))
                );
                return $context;
            }

            yield $this->enqueuer->enqueue(
                TaskContext::create(new ProcessTask(
                    args: array_merge([
                        'git', 'add'
                    ], $task->paths()),
                ), $context)
            );

            yield $this->enqueuer->enqueue(
                TaskContext::create(new ProcessTask(
                    args: [
                        'git',
                        'commit',
                        '-m',
                        $task->message()
                    ]
                ), $context)
            );

            return $context;
        });
    }
}
