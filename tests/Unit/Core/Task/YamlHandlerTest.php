<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\Handler;
use Maestro2\Core\Task\YamlHandler;
use Maestro2\Core\Task\YamlTask;
use Symfony\Component\Yaml\Yaml;

class YamlHandlerTest extends HandlerTestCase
{
    protected function defaultContext(): Context
    {
        return Context::create([], [
            new CwdFact($this->workspace()->path())
        ]);
    }

    protected function createHandler(): Handler
    {
        return new YamlHandler();
    }

    public function testMergesArrayIntoYaml(): void
    {
        $this->workspace()->put('yaml.yml', Yaml::dump([
            'foobar' => 'barfoo',
            'barfoo' => 'foobar',
        ]));
        $this->runTask(new YamlTask(
            path: $this->workspace()->path('yaml.yml'),
            data: [
                'barbar' => 'booboo',
            ]
        ));

        self::assertEquals([
            'foobar' => 'barfoo',
            'barfoo' => 'foobar',
            'barbar' => 'booboo',
        ], Yaml::parse($this->workspace()->getContents('yaml.yml'), true));
    }

    public function testFilterByClosure(): void
    {
        $this->workspace()->put('yaml.yml', Yaml::dump([
            'foobar' => 'barfoo',
        ]));
        $this->runTask(new YamlTask(
            path: $this->workspace()->path('yaml.yml'),
            data: [
                'barbar' => 'booboo',
            ],
            filter: function (array $array) {
                unset($array['foobar']);
                return $array;
            }
        ));

        self::assertEquals([
            'barbar' => 'booboo',
        ], Yaml::parse($this->workspace()->getContents('yaml.yml'), true));
    }

    public function testFilterReturnsNonObject(): void
    {
        $this->runTask(new YamlTask(
            path: $this->workspace()->path('yaml.yml'),
            filter: function (array $array) {
                return null;
            }
        ));

        self::assertNull(Yaml::parse($this->workspace()->getContents('yaml.yml'), true));
    }

    public function testCreatesIfNotExists(): void
    {
        $this->runTask(new YamlTask(
            path: $this->workspace()->path('yaml.yml'),
            data: [],
        ));

        self::assertEquals('{  }', $this->workspace()->getContents('yaml.yml'));
    }
}
