<?php

namespace Maestro\Composer\Task;

use Amp\Promise;
use Maestro\Composer\ComposerJson;
use Maestro\Composer\ComposerPackages;
use Maestro\Composer\ComposerRunner;
use Maestro\Composer\Fact\ComposerJsonFact;
use Maestro\Core\Fact\GroupFact;
use Maestro\Core\Fact\PhpFact;
use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\Process\ProcessResult;
use Maestro\Core\Process\ProcessRunner;
use Maestro\Core\Queue\Enqueuer;
use Maestro\Core\Report\Report;
use Maestro\Core\Report\ReportPublisher;
use Maestro\Core\Report\TaskReportPublisher;
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
        private Enqueuer $enqueuer,
        private ReportPublisher $publisher
    ) {
    }

    public function taskFqn(): string
    {
        return ComposerTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof ComposerTask);
        return call(function (Filesystem $filesystem) use ($task, $context) {
            $runner = new ComposerRunner($task, $context, $this->enqueuer);
            $fact = yield $this->updateComposerJson($filesystem, $task, $context, $runner);

            if ($task->update() === true) {
                yield $runner->run(['update']);
            }

            return $context->withFact($fact);
        }, $context->service(Filesystem::class));
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
     * @return Promise<ComposerJsonFact>
     */
    private function updateComposerJson(Filesystem $filesystem, ComposerTask $task, Context $context, ComposerRunner $runner): Promise
    {
        return call(function () use ($filesystem, $task, $context, $runner) {
            if (!$filesystem->exists('composer.json')) {
                yield $this->enqueuer->enqueue(new TaskContext($this->createJsonTask($task), $context));
                return $this->composerFact($filesystem);
            }

            $fact = $this->composerFact($filesystem);

            if ($task->require()) {
                $updated = yield $this->require($context, $fact, $runner, $task);
                $fact = $fact->withUpdated(ComposerPackages::fromArray($updated));
            }

            if ($task->remove()) {
                yield $this->remove($runner, $task);
            }

            return $fact;
        });
    }

    /**
     * @return Promise<array<string,string>>
     */
    private function require(Context $context, ComposerJsonFact $fact, ComposerRunner $runner, ComposerTask $task): Promise
    {
        return call(function () use ($context, $fact, $runner, $task) {
            $toUpdate = $this->requiredPackages($task, $fact, $context);

            if (empty($toUpdate)) {
                return [];
            }

            $args = array_merge([
                'require',
            ], array_map(
                fn (string $package, string $version) => sprintf('%s:%s', $package, $version),
                array_keys($toUpdate),
                array_values($toUpdate)
            ));

            if ($task->dev()) {
                $args[] = '--dev';
            }

            $args[] = '--no-update';

            yield $runner->run($args);

            return $toUpdate;
        });
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

        $args[] = '--no-update';

        return $runner->run($args);
    }

    private function composerFact(Filesystem $filesystem): ComposerJsonFact
    {
        $composerJson = ComposerJson::fromProjectRoot(
            $filesystem->localPath()
        );

        return new ComposerJsonFact(
            autoloadPaths: $composerJson->autoloadPaths(),
            packages: $composerJson->packages(),
        );
    }

    /**
     * @return array<string,string>
     */
    private function requiredPackages(ComposerTask $task, ComposerJsonFact $fact, Context $context): array
    {
        return array_filter($task->require(), function (string $version, string $name) use ($fact, $context): bool  {
            if (!$fact->packages()->has($name)) {
                return true;
            }

            $packageVersion = $fact->packages()->get($name)->version();

            if ($packageVersion->greaterThanOrEqualTo($version)) {
                $context->service(TaskReportPublisher::class)->publish(
                    Report::info(sprintf(
                        'Package "%s" version "%s" already satisifies "%s"',
                        $name,
                        $packageVersion->__toString(),
                        $version
                    ))
                );
                return false;
            }

            return true;
        }, ARRAY_FILTER_USE_BOTH);
    }
}
