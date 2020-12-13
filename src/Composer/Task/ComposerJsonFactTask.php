<?php

namespace Maestro2\Composer\Task;

use Maestro2\Core\Task\Task;
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
