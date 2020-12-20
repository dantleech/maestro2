<?php

namespace Maestro2\Core\HttpClient;

use Amp\ByteStream\InMemoryStream;
use Amp\CancellationToken;
use Amp\Http\Client\ApplicationInterceptor;
use Amp\Http\Client\DelegateHttpClient;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Amp\Promise;
use Amp\Success;

class TestHttpClientInterceptor implements ApplicationInterceptor
{
    /**
     * @var array{Request,Response}|[]
     */
    private array $responses = [];

    public function expect(Response $response): void
    {
        $this->responses[] = [$response->getRequest(), $response];
    }

    /**
     * {@inheritDoc}
     */
    public function request(
        Request $request,
        CancellationToken $cancellation,
        DelegateHttpClient $httpClient
    ): Promise {
        foreach ($this->responses as [ $responseRequest, $response ]) {
            if ($request->getUri()->__toString() === $responseRequest->getUri()->__toString()) {
                return new Success($response);
            }
        }

        return new Success(new Response(
            '1.1',
            404,
            'Not Found',
            [],
            new InMemoryStream(),
            $request
        ));
    }
}
