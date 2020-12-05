<?php

namespace Maestro2\Core\Queue;

use Maestro2\Core\Task\Task;

interface Dequeuer
{
    public function dequeue(): ?Task;
}
