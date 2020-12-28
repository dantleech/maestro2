<?php

namespace Maestro\Core\Task;

use Closure;

class ConditionalTask implements Task
{
    public function __construct(private Closure $predicate, private Task $task, private ?string $message = null)
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

    public function message(): ?string
    {
        return $this->message;
    }
}
