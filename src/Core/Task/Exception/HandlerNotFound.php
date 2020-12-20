<?php

namespace Maestro\Core\Task\Exception;

use RuntimeException;

class HandlerNotFound extends RuntimeException
{
    public static function forTask(string $task): self
    {
        return new self(sprintf(
            'Handler for task "%s" not found',
            $task
        ));
    }
}
