<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Generator;
use Maestro\Core\Fact\CwdFact;
use Maestro\Core\Fact\GroupFact;
use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\Report\ReportTablePublisher;
use Maestro\Core\Vcs\Repository;
use Maestro\Core\Vcs\RepositoryFactory;
use function Amp\call;

class GitSurveyHandler implements Handler
{
    public function __construct(private Filesystem $filesystem, private RepositoryFactory $repository, private ReportTablePublisher $publisher)
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
                $context->fact(GroupFact::class)->group(),
                $this->repository->create(
                    $this->filesystem->localPath($context->fact(CwdFact::class)->cwd())
                )
            );

            return $context;
        });
    }

    private function survey(string $group, Repository $repository): Generator
    {
        $headId = yield $repository->headId();
        $latestTag = (yield $repository->listTags())->mostRecent();
        $nbCommitsAhead = count(yield $repository->commitsBetween(
            $latestTag ? $latestTag->commitId() : $headId,
            $headId
        ));
        $message = yield $repository->message($headId);
        $this->publisher->publishTableRow(
            $group,
            [
                'tag' => $latestTag?->name() ?: '<none>',
                '+' => sprintf('+%s', $nbCommitsAhead),
                'message' => $message,
            ]
        );
    }
}
