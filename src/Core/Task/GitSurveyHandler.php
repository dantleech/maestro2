<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Queue\Enqueuer;
use Maestro2\Core\Report\ReportPublisher;

class GitSurveyHandler implements Handler
{
    public function __construct(private Enqueuer $enqueuer, private ReportPublisher $publisher)
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
        return call(function () use ($task, $context) {
            // most recent tag
            // most recent commit
        });
    }
}
