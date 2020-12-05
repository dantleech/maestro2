<?php

namespace Maestro2\Core\Config;

class RepositoryNode
{
    private string $name;
    private string $url;
    private MainNode $main;
    private ?string $pipeline;

    public function __construct(MainNode $main, string $name, string $url, ?string $pipeline = null)
    {
        $this->name = $name;
        $this->url = $url;
        $this->main = $main;
        $this->pipeline = $pipeline;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function path(): string
    {
        return $this->main()->workspacePath() . '/' . $this->name();
    }

    public function url(): string
    {
        return $this->url;
    }

    public function main(): MainNode
    {
        return $this->main;
    }

    public function pipeline(): ?string
    {
        return $this->pipeline;
    }
}
