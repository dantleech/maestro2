<?php

namespace Maestro2\Tests\Unit\Core\Config;

use Maestro2\Core\Config\MainNode;
use PHPUnit\Framework\TestCase;

class MainNodeTest extends TestCase
{
    public function testReturnSelectedRepositories(): void
    {
        $config = MainNode::fromArray([
            'workspacePath' => '/test/path',
            'repositories' => [
                [
                    'name' => 'foobar',
                    'url' => 'http://example.com/barfoo',
                ],
                [
                    'name' => 'barfoo',
                    'url' => 'http://example.com/foobar',
                ]
            ]
        ]);

        self::assertCount(1, $config->withSelectedRepos(['foobar'])->selectedRepositories());
    }

    public function testExceptionOnUnknownSelectedRepsitories(): void
    {
        $this->expectExceptionMessage('not known');
        $config = MainNode::fromArray([
            'workspacePath' => '/test/path',
            'repositories' => [
                [
                    'name' => 'foobar',
                    'url' => 'http://example.com/barfoo',
                ],
                [
                    'name' => 'barfoo',
                    'url' => 'http://example.com/foobar',
                ]
            ]
        ]);

        $config->withSelectedRepos(['zaa'])->selectedRepositories();
    }
}
