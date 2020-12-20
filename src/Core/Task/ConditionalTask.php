<?php

namespace Maestro\Core\Task;

use Closure;

class ConditionalTask implements Task
{
    public function __construct(private Closure $predicate, private Task $task)
    {
    }

    /**
     * @return Closure(Context): bool
     */
    public function predicate(): Closure
    {
        return $this->predicate;
    }

    public function task(): Task
    {
        return $this->task;
    }
}
