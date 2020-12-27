<?php

namespace Maestro\Core\Task;

use Amp\Http\Client\HttpClient;
use Amp\Http\Client\Request;
use Amp\Promise;
use Generator;
use Webmozart\Assert\Assert;
use function Amp\call;

class HttpRequestHandler implements Handler
{
    public function __construct(private HttpClient $httpClient)
    {
    }

    public function taskFqn(): string
    {
        return HttpRequestTask::class;
    }

    /**
     * {@inheritDoc}
     */
    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof HttpRequestTask);

        return call(function () use ($task, $context) {
            $response = yield from (function (Request $request) use ($task): Generator {
                $request->setHeaders($task->headers());
                return yield $this->httpClient->request($request);
            })(new Request(
                $task->url(),
                $task->method(),
                $task->body()
            ));

            return $context->withResult($response);
        });
    }
}
