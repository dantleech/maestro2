<?php

namespace Maestro\Rector\Task;

use Maestro\Core\Task\Task;

class RectorComposerUpgradeTask implements Task
{
    public function __construct(
        private ?string $path = null,
        private ?string $rectorVersion = '0.8.52'
    ) {
    }

    public function path(): ?string
    {
        return $this->path;
    }

    public function rectorVersion(): ?string
    {
        return $this->rectorVersion;
    }
}
