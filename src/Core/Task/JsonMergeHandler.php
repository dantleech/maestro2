<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Amp\Success;
use JsonException;
use Maestro2\Core\Filesystem\Filesystem;
use Maestro2\Core\Task\Exception\TaskError;
use stdClass;

class JsonMergeHandler implements Handler
{
    public function __construct(private Filesystem $filesystem)
    {
    }

    public function taskFqn(): string
    {
        return JsonMergeTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof JsonMergeTask);

        $existingData = new stdClass();

        if ($this->filesystem->exists($task->path())) {
            $jsonContents = $this->filesystem->getContents($task->path());

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

        $this->filesystem->putContents(
            $task->path(),
            json_encode(
                $data,
                JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES
            ),
        );

        return new Success($context);
    }

    private function mergeData(object $existingData, array $data): object
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
