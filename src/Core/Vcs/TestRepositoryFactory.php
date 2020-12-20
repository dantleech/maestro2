<?php

namespace Maestro\Core\Vcs;

class TestRepositoryFactory implements RepositoryFactory
{
    public function create(string $path): Repository
    {
        return new TestRepository();
    }
}
