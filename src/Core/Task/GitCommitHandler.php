<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\Process\ProcessResult;
use Maestro\Core\Queue\Enqueuer;
use Maestro\Core\Report\Report;
use Maestro\Core\Report\TaskReportPublisher;
use Maestro\Core\Task\Exception\TaskError;
use function Amp\call;

class GitCommitHandler implements Handler
{
    public function __construct(
        private Enqueuer $enqueuer
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
                    cmd: ['git', 'rev-parse', '--show-toplevel'],
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

            yield $this->enqueuer->enqueue(
                TaskContext::create(new ProcessTask(
                    cmd: array_merge(['git', 'add'], $this->filterPaths(
                        $context->service(TaskReportPublisher::class),
                        $context->service(Filesystem::class),
                        $task->paths(),
                    ))
                ), $context)
            );

            $context = yield $this->enqueuer->enqueue(
                TaskContext::create(new ProcessTask(
                    allowFailure: true,
                    cmd: ['git', 'diff', '--staged', '--exit-code']
                ), $context)
            );
            assert($context instanceof Context);
            $result = $context->result();
            assert($result instanceof ProcessResult);

            if ($result->exitCode() === 0) {
                $context->service(TaskReportPublisher::class)->publish(
                    Report::warn(
                        'Git commit: no files modified, not comitting anything',
                    )
                );
                return $context;
            }

            yield $this->enqueuer->enqueue(
                TaskContext::create(new ProcessTask(
                    cmd: [
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

    /**
     * @param list<string> $paths
     * @return list<string>
     */
    private function filterPaths(TaskReportPublisher $publisher, Filesystem $filesystem, array $paths): array
    {
        return array_values(array_filter($paths, function (string $path) use ($paths, $publisher, $filesystem) {
            if (false === $filesystem->exists($path)) {
                $publisher->publish(
                    Report::warn(sprintf(
                        'Git commit: file/directory "%s" does not exist, ignoring',
                        $path
                    ))
                );
                return false;
            }

            return true;
        }));
    }
}
