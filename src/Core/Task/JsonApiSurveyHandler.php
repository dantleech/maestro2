<?php

namespace Maestro\Core\Task;

use Amp\Http\Client\HttpClient;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Amp\Promise;
use Generator;
use JsonException;
use Maestro\Core\Queue\Enqueuer;
use Maestro\Core\Report\TaskReportPublisher;
use Maestro\Core\Task\Exception\TaskError;
use Webmozart\Assert\Assert;
use function Amp\call;

class JsonApiSurveyHandler implements Handler
{
    public function __construct(private Enqueuer $enqueuer)
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
            $data = (yield $this->enqueuer->enqueue(new TaskContext(
                new JsonApiTask(
                    url: $task->url(),
                    headers: $task->headers(),
                ),
                $context
            )))->result();
            Assert::isArray($data, 'JSON API did not return an array');
            $data = $task->extract()->__invoke($data);
            $context->service(TaskReportPublisher::class)->publishTableRow(
                $data
            );

            return $context;
        });
    }
}
