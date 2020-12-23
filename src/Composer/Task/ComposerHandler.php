<?php

namespace Maestro\Composer\Task;

use Amp\Promise;
use Maestro\Composer\ComposerJson;
use Maestro\Composer\ComposerRunner;
use Maestro\Composer\Fact\ComposerJsonFact;
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
            function (Filesystem $filesystem) use ($task, $context) {
                $runner = new ComposerRunner($task, $context, $this->enqueuer);

                $this->updateComposerJson($filesystem, $task, $context, $runner);

                if ($task->update() === true) {
                    yield $runner->run(['update']);
                }

                return $context->withFact($this->composerFact($filesystem));
            },
            $this->filesystem->cd($context->factOrNull(CwdFact::class)?->cwd() ?: '/')
        );
    }

    private function createJsonTask(ComposerTask $task): JsonMergeTask
    {
        $requireType = $task->dev() ? 'require-dev' : 'require';
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
     * @return Promise<void>
     */
    private function updateComposerJson(Filesystem $filesystem, ComposerTask $task, Context $context, ComposerRunner $runner): Promise
    {
        return call(function () use ($filesystem, $task, $context, $runner) {
            if (!$filesystem->exists('composer.json')) {
                yield $this->enqueuer->enqueue(new TaskContext($this->createJsonTask($task), $context));
                return;
            }

            if ($task->require()) {
                yield $this->require($runner, $task);
            }

            if ($task->remove()) {
                yield $this->remove($runner, $task);
            }
        });
    }

    /**
     * @return Promise<ProcessResult>
     */
    private function require(ComposerRunner $runner, ComposerTask $task): Promise
    {
        $args = array_merge([
            'require',
        ], array_map(
            fn (string $package, string $version) => sprintf('%s:%s', $package, $version),
            array_keys($task->require()),
            array_values($task->require())
        ));
        if ($task->dev()) {
            $args[] = '--dev';
        }
        return $runner->run($args);
    }

    /**
     * @return Promise<ProcessResult>
     */
    private function remove(ComposerRunner $runner, ComposerTask $task): Promise
    {
        $args = array_merge(
            ['remove'],
            array_values($task->remove())
        );

        if ($task->dev()) {
            $args[] = '--dev';
        }
        return $runner->run($args);
    }

    private function composerFact(Filesystem $filesystem): ComposerJsonFact
    {
        $composerJson = ComposerJson::fromProjectRoot(
            $filesystem->localPath()
        );

        return new ComposerJsonFact(
            autoloadPaths: $composerJson->autoloadPaths()
        );
    }
}
