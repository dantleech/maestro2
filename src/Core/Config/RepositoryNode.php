<?php

namespace Maestro2\Core\Config;

class RepositoryNode
{
    private string $name;
    private string $url;

    public function __construct(string $name, string $url)
    {
        $this->name = $name;
        $this->url = $url;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function url(): string
    {
        return $this->url;
    }
}
