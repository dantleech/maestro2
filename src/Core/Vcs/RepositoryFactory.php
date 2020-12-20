<?php

namespace Maestro\Core\Vcs;

interface RepositoryFactory
{
    public function create(string $path): Repository;
}
