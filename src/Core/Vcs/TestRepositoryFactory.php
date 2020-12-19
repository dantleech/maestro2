<?php

namespace Maestro2\Core\Vcs;

class TestRepositoryFactory implements RepositoryFactory
{
    public function create(string $path): Repository
    {
        return new TestRepository();
    }
}
