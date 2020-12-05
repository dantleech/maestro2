<?php

namespace Maestro2\Core\Task;

use Amp\Promise;

interface Handler
{
    public function taskFqn(): string;

    public function run(HandlerFactory $handlerFactory, Task $task): Promise;
}
