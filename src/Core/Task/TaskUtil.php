<?php

namespace Maestro\Core\Task;

use Stringable;

final class TaskUtil
{
    public static function describe(Task $task): string
    {
        return ($task instanceof Stringable) ? $task->__toString() : $task::class;
    }

    public static function describeShortName(Task $task): string
    {
        return ($task instanceof Stringable) ? $task->__toString() : substr(
            $task::class,
            (int)strrpos($task::class, '\\') + 1
        );
    }
}
