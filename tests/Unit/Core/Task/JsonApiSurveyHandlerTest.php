<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Amp\ByteStream\InMemoryStream;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Maestro2\Core\Task\Exception\TaskError;
use Maestro2\Core\Task\JsonApiSurveyTask;

class JsonApiSurveyHandlerTest extends HandlerTestCase
{
    public function testErrorOnNon200Response()
    {
        $this->expectException(TaskError::class);

        // by default test client will erturn 404
        $this->runTask(new JsonApiSurveyTask(
            url: 'https://www.example.com/foobar',
            extract: function (array $data) {
                return [
                    'foobar' => $data['foobar']
                ];
            }
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
        $this->runTask(new JsonApiSurveyTask(
            url: 'https://www.example.com/foobar',
            extract: function (array $data) {
                return [
                    'foobar' => $data['foobar']
                ];
            }
        ));
    }

    public function testExtractsDataFromJsonApi()
    {
        $this->httpClient()->expect(new Response(
            '1.1',
            200,
            null,
            [],
            new InMemoryStream('{"foobar": "barfoo"}'),
            new Request('https://www.example.com/foobar')
        ));

        $context = $this->runTask(new JsonApiSurveyTask(
            url: 'https://www.example.com/foobar',
            extract: function (array $data) {
                return [
                    'foobar' => $data['foobar']
                ];
            }
        ));

        self::assertCount(1, $this->reportManager()->table()->rows());
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

        $context = $this->runTask(new JsonApiSurveyTask(
            url: 'https://www.example.com/foobar',
            headers: [
                'Foobar' => 'Barfoo',
            ],
            extract: function (array $data) {
                return [
                    'foobar' => $data['foobar']
                ];
            }
        ));

        self::assertCount(1, $this->reportManager()->table()->rows());
    }
}
