<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Task\Exception\TaskError;
use Maestro2\Core\Task\Handler;
use Maestro2\Core\Task\JsonMergeHandler;
use Maestro2\Core\Task\JsonMergeTask;
use Maestro2\Tests\IntegrationTestCase;
use PHPUnit\Framework\TestCase;
use stdClass;

class JsonMergeHandlerTest extends HandlerTestCase
{
    protected function createHandler(): Handler
    {
        return new JsonMergeHandler();
    }

    public function testMergesArrayIntoJsonObject(): void
    {
        $this->workspace()->put('json.json', json_encode([
            'foobar' => 'barfoo',
            'barfoo' => 'foobar',
        ]));
        $this->runTask(new JsonMergeTask(
            path: $this->workspace()->path('json.json'),
            data: [
                'barbar' => 'booboo',
            ]
        ));

        self::assertEquals([
            'foobar' => 'barfoo',
            'barfoo' => 'foobar',
            'barbar' => 'booboo',
        ], json_decode($this->workspace()->getContents('json.json'), true));
    }

    public function testFilterByClosure(): void
    {
        $this->workspace()->put('json.json', json_encode([
            'foobar' => 'barfoo',
        ]));
        $this->runTask(new JsonMergeTask(
            path: $this->workspace()->path('json.json'),
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
        ], json_decode($this->workspace()->getContents('json.json'), true));
    }

    public function testFilterReturnsNonObject(): void
    {
        $this->runTask(new JsonMergeTask(
            path: $this->workspace()->path('json.json'),
            filter: function (stdClass $object) {
                return null;
            }
        ));

        self::assertNull(json_decode($this->workspace()->getContents('json.json'), true));
    }

    public function testCreatesIfNotExists(): void
    {
        $this->runTask(new JsonMergeTask(
            path: $this->workspace()->path('json.json'),
            data: [],
        ));

        self::assertEquals('{}', $this->workspace()->getContents('json.json'));
    }
}
