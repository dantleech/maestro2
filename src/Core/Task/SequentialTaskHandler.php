<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use function Amp\call;

class SequentialTaskHandler implements Handler
{
    public function taskFqn(): string
    {
        return SequentialTask::class;
    }

    public function run(HandlerFactory $factory, Task $task): Promise
    {
        return call(function (HandlerFactory $factory, Task $task) {
            assert($task instanceof SequentialTask);
            yield $this->dispatchTasks($factory, $task->tasks());
        }, $factory, $task);
    }

    private function dispatchTasks(HandlerFactory $factory, array $tasks): Promise
    {
        return call(function (HandlerFactory $factory, array $tasks) {
            while ($task = array_shift($tasks)) {
                yield $factory->handlerFor($task)->run($factory, $task);
            }
        }, $factory, $tasks);
    }
}
