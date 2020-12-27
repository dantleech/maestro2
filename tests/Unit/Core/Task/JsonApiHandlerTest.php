<?php

namespace Maestro\Tests\Unit\Core\Task;

use Amp\ByteStream\InMemoryStream;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Maestro\Core\Task\Exception\TaskError;
use Maestro\Core\Task\JsonApiTask;

class JsonApiHandlerTest extends HandlerTestCase
{
    public function testErrorOnNon200Response()
    {
        $this->expectException(TaskError::class);

        // by default test client will erturn 404
        $this->runTask(new JsonApiTask(
            url: 'https://www.example.com/foobar',
        ));
    }

    public function testErrorOnInvalidJson()
    {
        $this->httpClient()->expect(new Response(
            '1.1',
            200,
            null,
            [],
            new InMemoryStream('foobar": 123'),
            new Request('https://www.example.com/foobar')
        ));
        $this->expectException(TaskError::class);

        // by default test client will erturn 404
        $this->runTask(new JsonApiTask(
            url: 'https://www.example.com/foobar',
        ));
    }

    public function testReturnArrayFromJsonResponse()
    {
        $this->httpClient()->expect(new Response(
            '1.1',
            200,
            null,
            [],
            new InMemoryStream('{"foobar": "barfoo"}'),
            new Request('https://www.example.com/foobar')
        ));

        $context = $this->runTask(new JsonApiTask(
            url: 'https://www.example.com/foobar',
        ));

        self::assertEquals([
            'foobar' => 'barfoo',
        ], $context->result());
    }

    public function testPassesHeaders()
    {
        $this->httpClient()->expect(new Response(
            '1.1',
            200,
            null,
            [
                'Foobar' => 'Barfoo',
            ],
            new InMemoryStream('{"foobar": "barfoo"}'),
            new Request('https://www.example.com/foobar')
        ));

        $context = $this->runTask(new JsonApiTask(
            url: 'https://www.example.com/foobar',
            headers: [
                'Foobar' => 'Barfoo',
            ],
        ));

        self::assertEquals([
            'foobar' => 'barfoo',
        ], $context->result());
    }
}
