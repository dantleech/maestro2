<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Generator;
use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Fact\GroupFact;
use Maestro2\Core\Filesystem\Filesystem;
use Maestro2\Core\Report\ReportTablePublisher;
use Maestro2\Core\Vcs\Repository;
use Maestro2\Core\Vcs\RepositoryFactory;
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
                'then' => sprintf('+ %s', $nbCommitsAhead),
                'message' => $message,
            ]
        );
    }
}
