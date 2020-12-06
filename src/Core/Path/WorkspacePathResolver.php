<?php

namespace Maestro2\Core\Path;

use Webmozart\PathUtil\Path;

class WorkspacePathResolver
{
    public function __construct(private string $workspacePath)
    {
    }

    public function resolve(string $path): string
    {
        if (Path::isAbsolute($path)) {
            return $path;
        }
        return Path::join([$this->workspacePath, $path ]);
    }
}
