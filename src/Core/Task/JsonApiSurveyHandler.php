<?php

namespace Maestro\Core\Task;

use Amp\Http\Client\HttpClient;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Amp\Promise;
use Generator;
use JsonException;
use Maestro\Core\Fact\GroupFact;
use Maestro\Core\Report\ReportTablePublisher;
use Maestro\Core\Task\Exception\TaskError;
use Webmozart\Assert\Assert;
use function Amp\call;

class JsonApiSurveyHandler implements Handler
{
    public function __construct(private HttpClient $httpClient, private ReportTablePublisher $publisher)
    {
    }

    public function taskFqn(): string
    {
        return JsonApiSurveyTask::class;
    }

    /**
     * {@inheritDoc}
     */
    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof JsonApiSurveyTask);

        return call(function () use ($task, $context) {
            $response = yield from (function (Request $request) use ($task): Generator {
                $request->setHeaders($task->headers());
                /** @var Response $response */
                return yield $this->httpClient->request($request);
            })(new Request(
                $task->url()
            ));

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
            Assert::isArray($data, 'JSON API did not return an array');
            $data = $task->extract()->__invoke($data);
            $this->publisher->publishTableRow(
                $context->fact(GroupFact::class)->group(),
                $data
            );

            return $context;
        });
    }
}
