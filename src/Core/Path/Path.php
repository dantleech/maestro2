<?php

namespace Maestro2\Core\Path;

use Webmozart\PathUtil\Path as WebmozartPath;

final class Path
{
    public function __construct(private string $root, private ?string $cwd = null)
    {
    }

    public function resolve(string $path): string
    {
        return WebmozartPath::join([
            $this->root,
            $this->resolvePath($path)
        ]);
    }

    private function resolvePath(string $path): string
    {
        if (WebmozartPath::isAbsolute($path)) {
            return $path;
        }

        if (!$this->cwd) {
            return $path;
        }

        return WebmozartPath::makeAbsolute($path, $this->cwd);
    }
}
