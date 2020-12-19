<?php

namespace Maestro2\Core\Pipeline;

use Maestro2\Core\Inventory\MainNode;
use Maestro2\Core\Task\Task;

interface Pipeline
{
    public function build(MainNode $mainNode): Task;
}
