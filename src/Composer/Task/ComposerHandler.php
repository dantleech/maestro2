<?php

namespace Maestro\Composer\Task;

use Amp\Promise;
use Maestro\Composer\ComposerRunner;
use Maestro\Core\Fact\CwdFact;
use Maestro\Core\Fact\PhpFact;
use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\Process\ProcessResult;
use Maestro\Core\Process\ProcessRunner;
use Maestro\Core\Queue\Enqueuer;
use Maestro\Core\Task\Context;
use Maestro\Core\Task\Handler;
use Maestro\Core\Task\JsonMergeTask;
use Maestro\Core\Task\Task;
use Maestro\Core\Task\TaskContext;
use Symfony\Component\Process\ExecutableFinder;
use Webmozart\PathUtil\Path;
use stdClass;
use function Amp\call;

class ComposerHandler implements Handler
{
    public function __construct(
        private Filesystem $filesystem,
        private Enqueuer $enqueuer,
    ) {
    }

    public function taskFqn(): string
    {
        return ComposerTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof ComposerTask);
        return call(
            function (string $requireType, Filesystem $filesystem) use ($task, $context) {
                $runner = new ComposerRunner($task, $context, $this->enqueuer);

                if (!$filesystem->exists('composer.json')) {
                    yield $this->enqueuer->enqueue(new TaskContext($this->createJsonTask($task, $requireType), $context));
                } else {
                    if ($task->require()) {
                        yield $this->require($runner, $task);
                    }

                    if ($task->remove()) {
                        yield $this->remove($runner, $task);
                    }
                }

                if ($task->update() === true) {
                    yield $runner->run(['update']);
                }

                return $context;
            },
            $task->dev() ? 'require-dev' : 'require',
            $this->filesystem->cd($context->factOrNull(CwdFact::class)?->cwd() ?: '/')
        );
    }

    private function createJsonTask(ComposerTask $task, string $requireType): JsonMergeTask
    {
        return new JsonMergeTask(
            path: 'composer.json',
            data: [
                $requireType => $task->require()
            ],
            filter: static function (stdClass $object) use ($task, $requireType) {
                foreach ($object->$requireType as $package => $version) {
                    if (in_array($package, $task->remove())) {
                        unset($object->$requireType->$package);
                    }
                }
                if (is_array($object->{$requireType})) {
                    $object->{$requireType} = (object)$object->{$requireType};
                }

                return $object;
            }
        );
    }

    /**
     * @return Promise<ProcessResult>
     */
    private function require(ComposerRunner $runner, ComposerTask $task): Promise
    {
        return $runner->run(array_merge([
            'require',
        ], array_map(
            fn (string $package, string $version) => sprintf('%s:%s', $package, $version),
            array_keys($task->require()),
            array_values($task->require())
        )));
    }

    /**
     * @return Promise<ProcessResult>
     */
    private function remove(ComposerRunner $runner, ComposerTask $task): Promise
    {
        return $runner->run(array_merge(
            ['remove'],
            array_values($task->remove())
        ));
    }
}
