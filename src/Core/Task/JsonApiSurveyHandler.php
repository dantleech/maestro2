<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Maestro\Core\Queue\Enqueuer;
use Maestro\Core\Report\TaskReportPublisher;
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
