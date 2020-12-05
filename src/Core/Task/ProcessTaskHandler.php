<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Process\ProcessRunner;

class ProcessTaskHandler implements Handler
{
    public function __construct(private ProcessRunner $processRunner)
    {
    }

    public function taskFqn(): string
    {
        return ProcessTask::class;
    }

    public function run(Task $task): Promise
    {
        assert($task instanceof ProcessTask);
        return $this->processRunner->run($task->args(), $task->cwd());
    }
}
