<?php

namespace Maestro2\Core\Config;

use Webmozart\PathUtil\Path;

class RepositoryNode
{
    private string $name;
    private string $url;
    private MainNode $main;

    public function __construct(MainNode $main, string $name, string $url)
    {
        $this->name = $name;
        $this->url = $url;
        $this->main = $main;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function path(?string $subPath = null): string
    {
        return (function (string $path) use ($subPath) {
            if ($subPath) {
                return Path::join([$path, $subPath]);
            }

            return $path;
        })(Path::join([$this->main()->workspacePath(), $this->name() ]));
    }

    public function url(): string
    {
        return $this->url;
    }

    public function main(): MainNode
    {
        return $this->main;
    }
}
