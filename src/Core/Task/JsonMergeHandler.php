<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Amp\Success;
use JsonException;
use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\Task\Exception\TaskError;
use Webmozart\Assert\Assert;
use stdClass;

class JsonMergeHandler implements Handler
{
    public function __construct()
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
        $filesystem = $context->service(Filesystem::class);

        if ($filesystem->exists($task->path())) {
            $jsonContents = $filesystem->getContents($task->path());

            try {
                /** @var mixed $existingData */
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

        Assert::isInstanceOf($existingData, stdClass::class, 'JSON document must be an object');

        $data = $this->mergeData(clone $existingData, $task->data());

        if ($filter = $task->filter()) {
            $data = $filter($existingData);
        }

        if ($data === $existingData) {
            return new Success($context);
        }

        $filesystem->putContents(
            $task->path(),
            json_encode(
                $data,
                JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES
            ),
        );

        return new Success($context);
    }

    private function mergeData(stdClass $existingData, array $data): object
    {
        /** @var mixed $value */
        foreach ($data as $key => $value) {
            if (!property_exists($existingData, (string)$key)) {
                $existingData->$key = [];
            }

            /** @var mixed $existingValue */
            $existingValue = $existingData->$key;

            if (is_array($value) && $existingValue instanceof stdClass) {
                $this->mergeData($existingValue, $value);
                continue;
            }

            $existingData->$key = $value;
        }

        return $existingData;
    }
}
