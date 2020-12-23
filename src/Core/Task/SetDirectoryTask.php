<?php

namespace Maestro\Core\Task;

class SetDirectoryTask implements DelegateTask
{
    public function __construct(private string $path)
    {
    }

    public function path(): string
    {
        return $this->path;
    }

    public function task(): Task
    {
        return new ClosureTask(
            closure: function (array $args, Context $context) {
            }
        );
    }
}
