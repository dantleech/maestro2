<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Process\ProcessRunner;
use Maestro2\Core\Queue\Enqueuer;
use function Amp\call;

class CommandsTaskHandler implements Handler
{
    public function __construct(private Enqueuer $enqueuer)
    {
    }

    public function taskFqn(): string
    {
        return CommandsTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof CommandsTask);
        return call(function () use ($task, $context) {
            foreach ($task->commands() as $command) {
                $result = yield $this->enqueuer->enqueue(
                    TaskContext::create(new ProcessTask(
                        group: $task->group(),
                        args: $command,
                        cwd: $task->cwd()
                    ), $context)
                );

                if ($task->failFast() && false === $result->isOk()) {
                    break;
                }
            }

            return $context;
        });
    }
}
