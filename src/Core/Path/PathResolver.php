<?php

namespace Maestro2\Core\Path;

use Webmozart\PathUtil\Path;

class PathResolver
{
    public function __construct(private string $basePath)
    {
    }

    public function resolve(string $path): string
    {
        return Path::join([$this->basePath, $path]);
    }
}
