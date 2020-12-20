<?php

namespace Maestro\Core\Pipeline;

use Maestro\Core\Inventory\MainNode;
use Maestro\Core\Task\Task;

interface Pipeline
{
    public function build(MainNode $mainNode): Task;
}
