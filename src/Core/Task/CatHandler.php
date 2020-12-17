<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Fact\GroupFact;
use Maestro2\Core\Filesystem\Filesystem;
use Maestro2\Core\Report\Report;
use Maestro2\Core\Report\ReportPublisher;
use function Amp\call;

class CatHandler implements Handler
{
    public function __construct(private Filesystem $filesystem, private ReportPublisher $publisher)
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
            $this->publisher->publish(
                $context->factOrNull(GroupFact::class)?->group() ?: 'workspace',
                Report::info(
                    sprintf('Contents of "%s"', $task->path()),
                    $this->filesystem->cd(
                        $context->factOrNull(CwdFact::class)?->cwd() ?: '/'
                    )->getContents($task->path())
                )
            );

            return $context;
        });
    }
}
