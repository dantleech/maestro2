<?php

namespace Maestro2\Tests;

use Maestro2\Composer\Extension\ComposerExtension;
use Maestro2\Core\Extension\CoreExtension;
use Maestro2\Core\Extension\TestExtension;
use PHPUnit\Framework\TestCase;
use Phpactor\Container\Container;
use Phpactor\Container\PhpactorContainer;
use Phpactor\TestUtils\Workspace;

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
            ComposerExtension::class,
        ], array_merge([
            CoreExtension::PARAM_TEMPLATE_PATH => $this->workspace()->path('templates'),
            CoreExtension::PARAM_WORKSPACE_PATH => $this->workspace()->path('workspace'),
            CoreExtension::PARAM_WORKING_DIRECTORY => $this->workspace()->path(),
        ], $config));
    }

    public function workspace(): Workspace
    {
        return new Workspace(__DIR__ . '/Workspace');
    }
}
