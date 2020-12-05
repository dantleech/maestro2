<?php

namespace Maestro2\Core\Task\Exception;

use RuntimeException;

class HandlerNotFound extends RuntimeException
{
    public static function forTask(string $task)
    {
        return new self(sprintf(
            'Handler for task "%s" not found',
            $task
        ));
    }
}
