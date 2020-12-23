<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Generator;
use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\Report\TaskReportPublisher;
use Maestro\Core\Vcs\Repository;
use Maestro\Core\Vcs\RepositoryFactory;
use function Amp\call;

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
                'tag' => $latestTagname() ?: '<none>',
                '+' => sprintf('+%s', $nbCommitsAhead),
                'message' => $message,
            ]
        );
    }
}
