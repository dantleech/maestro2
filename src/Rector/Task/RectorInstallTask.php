<?php

namespace Maestro\Rector\Task;

use Maestro\Core\Task\Task;

class RectorInstallTask implements Task
{
    public function __construct(
        private ?string $path = null,
        private ?string $version = '0.8.52'
    ) {
    }

    public function path(): ?string
    {
        return $this->path;
    }

    public function version(): ?string
    {
        return $this->version;
    }
}
