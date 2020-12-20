<?php

namespace Maestro2\Core\HttpClient;

use Amp\CancellationToken;
use Amp\Http\Client\ApplicationInterceptor;
use Amp\Http\Client\DelegateHttpClient;
use Amp\Http\Client\Request;
use Amp\Promise;
use Psr\Log\LoggerInterface;
use function Amp\call;

class LoggingHttpClientInterceptor implements ApplicationInterceptor
{
    public function __construct(private LoggerInterface $logger)
    {
    }
    /**
     * {@inheritDoc}
     */
    public function request(Request $request, CancellationToken $cancellation, DelegateHttpClient $httpClient): Promise
    {
        return call(function () use ($request, $cancellation, $httpClient) {
            $this->logger->info(sprintf(
                '>> [%s] %s %s',
                $request->getMethod(),
                $request->getUri()->__toString(),
                json_encode($request->getHeaders())
            ));
            $response = yield $httpClient->request($request, $cancellation);
            $this->logger->info(sprintf(
                '<< %s %s',
                $response->getStatus(),
                yield $response->getBody()->buffer()
            ));

            return $response;
        });
    }
}
