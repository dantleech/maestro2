<?php

namespace Maestro2\Tests;

use PHPUnit\Framework\TestCase;
use Phpactor\TestUtils\Workspace;

class IntegrationTestCase extends TestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function workspace(): Workspace
    {
        return new Workspace(__DIR__ . '/Workspace');
    }
}
