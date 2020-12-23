<?php

namespace Maestro\Core\Task;

use Maestro\Core\Filesystem\Filesystem;

interface ContextFactory
{
    public function createContext(): Context;
}
