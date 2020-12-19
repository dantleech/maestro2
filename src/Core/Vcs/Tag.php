<?php

namespace Maestro2\Core\Vcs;

class Tag
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $commitId;

    public function __construct(string $name, string $commitId)
    {
        $this->name = $name;
        $this->commitId = $commitId;
    }

    public function commitId(): string
    {
        return $this->commitId;
    }

    public function name(): string
    {
        return $this->name;
    }
}
