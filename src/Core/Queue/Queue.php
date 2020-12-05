<?php

namespace Maestro2\Core\Queue;

use Maestro2\Core\Task\Task;

class Queue implements Enqueuer, Dequeuer
{
    public function __construct(private array $tasks = [])
    {
    }

    public function enqueue(Task $task): void
    {
        $this->tasks[] = $task;
    }

    public function enqueueAll(array $tasks): void
    {
        foreach ($tasks as $task) {
            $this->enqueue($task);
        }
    }

    public function dequeue(): ?Task
    {
        return array_shift($this->tasks);
    }
}
