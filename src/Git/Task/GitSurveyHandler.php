<?php

namespace Maestro\Git\Task;

use Amp\Promise;
use Generator;
use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\Report\TaskReportPublisher;
use Maestro\Core\Task\Context;
use Maestro\Core\Task\Handler;
use Maestro\Core\Task\Task;
use Maestro\Core\Vcs\Repository;
use Maestro\Core\Vcs\RepositoryFactory;
use function Amp\call;
use Maestro\Git\Task\GitSurveyTask;

class GitSurveyHandler implements Handler
{
    public function __construct(private RepositoryFactory $repository)
    {
    }

    public function taskFqn(): string
    {
        return GitSurveyTask::class;
    }

    /**
     * {@inheritDoc}
     */
    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof GitSurveyTask);
        return call(function () use ($context) {
            yield from $this->survey(
                $context->service(TaskReportPublisher::class),
                $this->repository->create(
                    $context->service(Filesystem::class)->localPath()
                )
            );

            return $context;
        });
    }

    private function survey(TaskReportPublisher $publisher, Repository $repository): Generator
    {
        $headId = yield $repository->headId();
        $latestTag = (yield $repository->listTags())->mostRecent();
        $nbCommitsAhead = count(yield $repository->commitsBetween(
            $latestTag ? $latestTag->commitId() : $headId,
            $headId
        ));
        $message = yield $repository->message($headId);
        $publisher->publishTableRow(
            [
                'tag' => $latestTag?->name() ?: '<none>',
                '+' => sprintf('+%s', $nbCommitsAhead),
                'message' => $message,
            ]
        );
    }
}
