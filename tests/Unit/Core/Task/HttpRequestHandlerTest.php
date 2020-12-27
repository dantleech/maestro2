<?php

namespace Maestro\Tests\Unit\Core\Task;

use Amp\ByteStream\InMemoryStream;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Maestro\Core\Task\HttpRequestTask;

class HttpRequestHandlerTest extends HandlerTestCase
{
    public function testMakeRequest(): void
    {
        $response = new Response(
            '1.1',
            200,
            null,
            [],
            new InMemoryStream('{"foobar": "barfoo"}'),
            new Request('https://www.example.com/foobar')
        );
        $this->httpClient()->expect($response);

        $context = $this->runTask(new HttpRequestTask(
            url: 'https://www.example.com/foobar',
        ));

        self::assertSame($response, $context->result());
    }

    public function testMakeFullRequest(): void
    {
        $response = new Response(
            '1.1',
            200,
            null,
            [],
            new InMemoryStream('{"foobar": "barfoo"}'),
            new Request('https://www.example.com/foobar', 'POST')
        );
        $this->httpClient()->expect($response);

        $context = $this->runTask(new HttpRequestTask(
            url: 'https://www.example.com/foobar',
            method: 'POST',
        ));

        self::assertSame($response, $context->result());
    }
}
