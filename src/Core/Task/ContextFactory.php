<?php

namespace Maestro\Core\Task;

interface ContextFactory
{
    public function createContext(): Context;
}
