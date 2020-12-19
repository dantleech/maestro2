<?php

namespace Maestro2\Core\Vcs;

interface RepositoryFactory
{
    public function create(string $path): Repository;
}
