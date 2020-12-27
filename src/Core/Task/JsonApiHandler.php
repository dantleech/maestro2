<?php

namespace Maestro\Core\Task;

use Amp\Http\Client\Response;
use Amp\Promise;
use JsonException;
use Maestro\Core\Queue\Enqueuer;
use Maestro\Core\Task\Exception\TaskError;
use Webmozart\Assert\Assert;
use function Amp\call;

class JsonApiHandler implements Handler
{
    public function __construct(private Enqueuer $enqueuer)
    {
    }

    public function taskFqn(): string
    {
        return JsonApiTask::class;
    }

    /**
     * {@inheritDoc}
     */
    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof JsonApiTask);
        return call(function () use ($task, $context) {
            $body = (function (?array $body):? string {
                if (null === $body) {
                    return null;
                }

                $decoded = json_encode($body);
                Assert::string($decoded);
                return $decoded;
            })($task->body());

            $response = (yield $this->enqueuer->enqueue(
                new TaskContext(
                    new HttpRequestTask(
                        url: $task->url(),
                        method: $task->method(),
                        body: $body,
                        headers: $task->headers()
                    ),
                    $context
                )
            ))->result();
            Assert::isInstanceOf($response, Response::class);

            if ('2' !== substr((string)$response->getStatus(), 0, 1)) {
                throw new TaskError(sprintf(
                    '%s return non successful response code "%s"',
                    $task->url(),
                    $response->getStatus()
                ));
            }

            try {
                $data = json_decode(yield $response->getBody()->buffer(), true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $decodingError) {
                throw new TaskError(sprintf(
                    'Could not decode JSON from URL "%s": %s',
                    $task->url(),
                    $decodingError->getMessage()
                ));
            }

            return $context->withResult($data);
        });
    }

}
