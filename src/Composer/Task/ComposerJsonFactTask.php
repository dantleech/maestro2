<?php

namespace Maestro\Composer\Task;

use Maestro\Core\Task\Task;
use Stringable;

class ComposerJsonFactTask implements Task, Stringable
{
    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return 'Extracting facts from `composer.json`';
    }
}
