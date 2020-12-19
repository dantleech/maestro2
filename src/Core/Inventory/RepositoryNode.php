<?php

namespace Maestro2\Core\Inventory;

class RepositoryNode
{
    private string $name;
    private string $url;
    private MainNode $main;
    private array $vars;

    public function __construct(MainNode $main, string $name, string $url, array $vars = [])
    {
        $this->name = $name;
        $this->url = $url;
        $this->main = $main;
        $this->vars = $vars;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function main(): MainNode
    {
        return $this->main;
    }

    public function vars(): Vars
    {
        return $this->main()->vars()->merge(new Vars($this->vars));
    }
}
