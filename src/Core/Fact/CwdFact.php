<?php

namespace Maestro2\Core\Fact;

use Webmozart\PathUtil\Path;

class CwdFact implements Fact
{
    public function __construct(private string $cwd)
    {
    }

    public function cwd(): string
    {
        return $this->cwd;
    }
}
