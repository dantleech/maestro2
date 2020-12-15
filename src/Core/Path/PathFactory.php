<?php

namespace Maestro2\Core\Path;

use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Task\Context;

class PathFactory
{
    private string $workspacePath;

    public function __construct(string $workspacePath)
    {
        $this->workspacePath = $workspacePath;
    }

    public function for(Context $context): Path
    {
        return new Path($this->workspacePath, $context->factOrNull(CwdFact::class)?->cwd());
    }
}
