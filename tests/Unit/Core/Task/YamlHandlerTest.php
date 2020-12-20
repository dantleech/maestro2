<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Task\YamlTask;
use Symfony\Component\Yaml\Yaml;

class YamlHandlerTest extends HandlerTestCase
{
    public function testMergesArrayIntoYaml(): void
    {
        $this->filesystem()->putContents('yaml.yml', Yaml::dump([
            'foobar' => 'barfoo',
            'barfoo' => 'foobar',
        ]));
        $this->runTask(new YamlTask(
            path: 'yaml.yml',
            data: [
                'barbar' => 'booboo',
            ]
        ));

        self::assertEquals([
            'foobar' => 'barfoo',
            'barfoo' => 'foobar',
            'barbar' => 'booboo',
        ], Yaml::parse($this->filesystem()->getContents('yaml.yml'), true));
    }

    public function testFilterByClosure(): void
    {
        $this->filesystem()->putContents('yaml.yml', Yaml::dump([
            'foobar' => 'barfoo',
        ]));
        $this->runTask(new YamlTask(
            path: 'yaml.yml',
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
        ], Yaml::parse($this->filesystem()->getContents('yaml.yml'), true));
    }

    public function testFilterReturnsNonObject(): void
    {
        $this->runTask(new YamlTask(
            path: 'yaml.yml',
            filter: function (array $array) {
                return null;
            }
        ));

        self::assertNull(Yaml::parse($this->filesystem()->getContents('yaml.yml'), true));
    }

    public function testCreatesIfNotExists(): void
    {
        $this->runTask(new YamlTask(
            path: 'yaml.yml',
            data: [],
        ));

        self::assertEquals('{  }', $this->filesystem()->getContents('yaml.yml'));
    }
}
