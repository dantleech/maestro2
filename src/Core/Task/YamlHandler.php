<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Amp\Success;
use Exception;
use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Filesystem\Filesystem;
use Maestro2\Core\Task\Exception\TaskError;
use Symfony\Component\Yaml\Yaml;

class YamlHandler implements Handler
{
    public function __construct(private Filesystem $filesystem)
    {
    }

    public function taskFqn(): string
    {
        return YamlTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof YamlTask);
        $this->runYaml($this->filesystem->cd(
            $context->factOrNull(CwdFact::class)?->cwd() ?: '/'
        ), $task);

        return new Success($context);
    }

    private function runYaml(Filesystem $filesystem, YamlTask $task): void
    {
        $existingData = [];

        if ($filesystem->exists($task->path())) {
            try {
                $existingData = Yaml::parse($filesystem->getContents($task->path()));
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

        $this->filesystem->putContents(
            $task->path(),
            Yaml::dump($data, $task->inline())
        );
    }
}
