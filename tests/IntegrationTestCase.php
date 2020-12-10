<?php

namespace Maestro2\Tests;

use Amp\Loop;
use Maestro2\Core\Extension\CoreExtension;
use Maestro2\Core\Extension\TestExtension;
use Maestro2\Core\Queue\TestEnqueuer;
use PHPUnit\Framework\TestCase;
use Phpactor\Container\Container;
use Phpactor\Container\PhpactorContainer;
use Phpactor\TestUtils\Workspace;
use Symfony\Component\Debug\Debug;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Throwable;

class IntegrationTestCase extends TestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    protected function container(array $config = []): Container
    {
        return PhpactorContainer::fromExtensions([
            CoreExtension::class,
            TestExtension::class,
        ], array_merge([
            'core.path.config' => $this->workspace()->path(),
        ], $config));
    }

    public function workspace(): Workspace
    {
        return new Workspace(__DIR__ . '/Workspace');
    }
}
