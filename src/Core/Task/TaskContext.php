<?php

namespace Maestro2\Core\Task;

final class TaskContext
{
    public function __construct(private Task $task, private Context $context)
    {
    }

    public static function create(Task $task, ?Context $context = null): self
    {
        return new self($task, $context ?: Context::create());
    }

    public function context(): Context
    {
        return $this->context;
    }

    public function task(): Task
    {
        return $this->task;
    }
}
