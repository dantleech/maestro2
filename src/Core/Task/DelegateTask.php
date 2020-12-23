<?php

namespace Maestro\Core\Task;

interface DelegateTask extends Task
{
    public function task(): Task;
}
