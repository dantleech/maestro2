<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Fact\GroupFact;
use Maestro2\Core\Filesystem\Filesystem;
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
        private ReportPublisher $publisher,
        private Filesystem $filesystem
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

            yield $this->enqueuer->enqueue(
                TaskContext::create(new ProcessTask(
                    args: array_merge(['git', 'add'], $this->filterPaths(
                        $context->fact(CwdFact::class)->cwd(),
                        $context->fact(GroupFact::class)->group(),
                        $task->paths(),
                    ))
                ), $context)
            );

            $context = yield $this->enqueuer->enqueue(
                TaskContext::create(new ProcessTask(
                    allowFailure: true,
                    args: [
                        'git',
                        'diff',
                        '--staged',
                        '--exit-code',
                    ]
                ), $context)
            );
            assert($context instanceof Context);
            $result = $context->result();
            assert($result instanceof ProcessResult);

            if ($result->exitCode() === 0) {
                $this->publisher->publish(
                    $context->fact(GroupFact::class)->group(),
                    Report::warn(
                        'Git commit: no files modified, not comitting anything',
                    )
                );
                return $context;
            }

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

    /**
     * @param list<string> $paths
     * @return list<string>
     */
    private function filterPaths(string $cwd, string $group, array $paths): array
    {
        return array_values(array_filter($paths, function (string $path) use ($group, $cwd) {
            if (false === $this->filesystem->cd($cwd)->exists($path)) {
                $this->publisher->publish(
                    $group,
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
