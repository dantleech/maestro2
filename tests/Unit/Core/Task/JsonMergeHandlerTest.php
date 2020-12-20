<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Task\JsonMergeTask;
use stdClass;

class JsonMergeHandlerTest extends HandlerTestCase
{
    public function testMergesArrayIntoJsonObject(): void
    {
        $this->filesystem()->putContents('json.json', json_encode([
            'foobar' => 'barfoo',
            'barfoo' => 'foobar',
        ]));
        $this->runTask(new JsonMergeTask(
            path: 'json.json',
            data: [
                'barbar' => 'booboo',
            ]
        ));

        self::assertEquals([
            'foobar' => 'barfoo',
            'barfoo' => 'foobar',
            'barbar' => 'booboo',
        ], json_decode($this->filesystem()->getContents('json.json'), true));
    }

    public function testFilterByClosure(): void
    {
        $this->filesystem()->putContents('json.json', json_encode([
            'foobar' => 'barfoo',
        ]));
        $this->runTask(new JsonMergeTask(
            path: 'json.json',
            data: [
                'barbar' => 'booboo',
            ],
            filter: function (stdClass $object) {
                unset($object->foobar);
                return $object;
            }
        ));

        self::assertEquals([
            'barbar' => 'booboo',
        ], json_decode($this->filesystem()->getContents('json.json'), true));
    }

    public function testFilterReturnsNonObject(): void
    {
        $this->runTask(new JsonMergeTask(
            path: 'json.json',
            filter: function (stdClass $object) {
                return null;
            }
        ));

        self::assertNull(json_decode($this->filesystem()->getContents('json.json'), true));
    }

    public function testCreatesIfNotExists(): void
    {
        $this->runTask(new JsonMergeTask(
            path: 'json.json',
            data: [],
        ));

        self::assertEquals('{}', $this->filesystem()->getContents('json.json'));
    }
}
