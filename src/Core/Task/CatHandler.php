<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\Report\Report;
use Maestro\Core\Report\TaskReportPublisher;
use function Amp\call;

class CatHandler implements Handler
{
    public function __construct()
    {
    }

    public function taskFqn(): string
    {
        return CatTask::class;
    }

    /**
     * {@inheritDoc}
     */
    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof CatTask);
        return call(function () use ($task, $context) {
            $context->service(TaskReportPublisher::class)->publish(
                Report::info(
                    sprintf('Contents of "%s"', $task->path()),
                    $context->service(Filesystem::class)->getContents($task->path())
                )
            );

            return $context;
        });
    }
}
