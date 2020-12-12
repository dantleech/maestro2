<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Amp\Success;
use Exception;
use Maestro2\Core\Task\Exception\TaskError;
use Symfony\Component\Yaml\Yaml;

class YamlHandler implements Handler
{
    public function taskFqn(): string
    {
        return YamlTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof YamlTask);

        $existingData = [];

        if (file_exists($task->path())) {
            try {
                $existingData = Yaml::parse(file_get_contents($task->path()));
            } catch (Exception $e) {
                throw new TaskError(sprintf(
                    'Could not parse YAML: "%s"',
                    $e->getMessage()
                ), 0, $e);
            }
        }

        $data = array_merge($existingData, $task->data());

        if ($filter = $task->filter()) {
            $data = $filter($data);
        }

        file_put_contents(
            $task->path(),
            Yaml::dump($data)
        );

        return new Success($context);
    }
}
