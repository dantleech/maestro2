<?php

namespace Maestro\Rector\Task;

use Amp\Promise;
use Maestro\Core\Fact\CwdFact;
use Maestro\Core\Queue\Enqueuer;
use Maestro\Composer\Task\ComposerTask;
use Maestro\Core\Task\Context;
use Maestro\Core\Task\Exception\TaskError;
use Maestro\Core\Task\Handler;
use Maestro\Core\Task\Task;
use Maestro\Core\Task\TaskContext;
use Maestro\Rector\Fact\RectorInstallFact;
use function Amp\call;

class RectorInstallHandler implements Handler
{
    private Enqueuer $enqueuer;

    public function __construct(Enqueuer $enqueuer)
    {
        $this->enqueuer = $enqueuer;
    }

    public function taskFqn(): string
    {
        return RectorInstallTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof RectorComposerUpgradeTask);

        return call(function (string $cwd) use ($task, $context) {
            yield $this->enqueuer->enqueue(
                TaskContext::create(
                    new ComposerTask(
                        require: [
                            'rector/rector-prefixed' => $task->rectorVersion(),
                        ],
                        update: true
                    ),
                    $context
                )
            );

            $binPath = $cwd . '/vendor/bin/rector';
            if (!file_exists($binPath)) {
                throw new TaskError(sprintf(
                    'Rector bin "%s" does not exist after rector installation',
                    $binPath
                ));
            }

            return $context->withFact(
                new RectorInstallFact(
                    binPath: $binPath
                )
            );
        }, $task->path() ?: $context->fact(CwdFact::class)->cwd());
    }

    private function rectorConfigPath(string $cwd, RectorComposerUpgradeTask $task): string
    {
        return $cwd . '/rector.php';
    }
}
