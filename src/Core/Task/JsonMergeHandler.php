<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Amp\Success;
use JsonException;
use Maestro2\Core\Task\Exception\TaskError;
use stdClass;

class JsonMergeHandler implements Handler
{
    public function taskFqn(): string
    {
        return JsonMergeTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof JsonMergeTask);

        $existingData = new stdClass();

        if (file_exists($task->path())) {
            $jsonContents = file_get_contents($task->path());

            try {
                $existingData = json_decode(
                    $jsonContents,
                    false,
                    JSON_FORCE_OBJECT
                );
            } catch (JsonException $e) {
                throw new TaskError(sprintf(
                    'Could not parse JSON: "%s"',
                    $jsonContents
                ), 0, $e);
            }
        }
        $data = $this->mergeData($existingData, $task->data());

        if ($filter = $task->filter()) {
            $data = $filter($existingData);
        }

        file_put_contents(
            $task->path(),
            json_encode(
                $data,
                JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES
            ),
        );

        return new Success();
    }

    private function mergeData(object $existingData, $data): object
    {
        foreach ($data as $key => $value) {
            if (!property_exists($existingData, $key)) {
                $existingData->$key = [];
            }

            if (is_array($value) && is_object($existingData->$key)) {
                $this->mergeData($existingData->$key, $value);
                continue;
            }

            $existingData->$key = $value;
        }

        return $existingData;
    }
}
