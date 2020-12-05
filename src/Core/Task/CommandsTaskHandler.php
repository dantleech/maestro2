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

    public function run(Task $task): Promise
    {
        assert($task instanceof CommandsTask);
        return call(function () use ($task) {
            foreach ($task->commands() as $command) {
                yield $this->enqueuer->enqueue(new ProcessTask(
                    args: $command,
                    cwd: $task->cwd()
                ));
            }
        });
    }
}
