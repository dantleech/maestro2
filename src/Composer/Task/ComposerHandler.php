<?php

namespace Maestro\Composer\Task;

use Amp\Promise;
use Composer\Semver\VersionParser;
use Maestro\Composer\ComposerJson;
use Maestro\Composer\ComposerPackages;
use Maestro\Composer\ComposerRunner;
use Maestro\Composer\Fact\ComposerJsonFact;
use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\Process\ProcessResult;
use Maestro\Core\Queue\Enqueuer;
use Maestro\Core\Report\Report;
use Maestro\Core\Report\ReportPublisher;
use Maestro\Core\Report\TaskReportPublisher;
use Maestro\Core\Task\Context;
use Maestro\Core\Task\Handler;
use Maestro\Core\Task\JsonMergeTask;
use Maestro\Core\Task\Task;
use Maestro\Core\Task\TaskContext;
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
            assert($fact instanceof ComposerJsonFact);

            if ($task->update() === true) {
                yield $runner->run(array_merge(
                    ['update'],
                    $fact->updated()->names()
                ));
            }

            return $context->withFact($fact);
        }, $context->service(Filesystem::class));
    }

    private function createJsonTask(ComposerTask $task): JsonMergeTask
    {
        return new JsonMergeTask(
            path: 'composer.json',
            data: (function (ComposerTask $task, array $composer) {
                if ($task->require()) {
                    $composer['require'] = $task->require();
                }
                if ($task->requireDev()) {
                    $composer['require-dev'] = $task->requireDev();
                }

                return $composer;
            })($task, [])
        );
    }

    /**
     * @return Promise<ComposerJsonFact>
     */
    private function updateComposerJson(Filesystem $filesystem, ComposerTask $task, Context $context, ComposerRunner $runner): Promise
    {
        return call(function () use ($filesystem, $task, $context, $runner) {
            $created = false;
            if (!$filesystem->exists('composer.json')) {
                $created = true;
                yield $this->enqueuer->enqueue(new TaskContext($this->createJsonTask($task), $context));
            }

            $fact = $this->composerFact($filesystem);

            if ($created) {
                return $fact->withUpdated($fact->packages());
            }

            $packages = new ComposerPackages([]);
            if ($task->require()) {
                $updated = yield $this->require($context, $fact, $runner, $task, false);
                $packages = $packages->merge(ComposerPackages::fromArray($updated, false));
            }
            if ($task->requireDev()) {
                $updated = yield $this->require($context, $fact, $runner, $task, true);
                $packages = $packages->merge(ComposerPackages::fromArray($updated, true));
            }

            $fact = $fact->withUpdated($packages);

            if ($task->remove()) {
                yield $this->remove($runner, $task);
            }

            return $fact;
        });
    }

    /**
     * @return Promise<array<string,string>>
     */
    private function require(Context $context, ComposerJsonFact $fact, ComposerRunner $runner, ComposerTask $task, bool $dev): Promise
    {
        return call(function () use ($context, $fact, $runner, $task, $dev) {
            $packages = $dev ? $task->requireDev() : $task->require();
            $toUpdate = $this->requiredPackages($packages, $task, $fact, $context);

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

            if ($dev) {
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
     * @param array<string,string> $packages
     * @return array<string,string>
     */
    private function requiredPackages(array $packages, ComposerTask $task, ComposerJsonFact $fact, Context $context): array
    {
        return array_filter($packages, function (string $version, string $name) use ($fact, $task, $context): bool {
            if (!$fact->packages()->has($name)) {
                return !$task->intersection();
            }

            $packageVersion = $fact->packages()->get($name)->version();

            if ($task->satisfactory()) {
                $parser = new VersionParser();
                $packageConstraints = $parser->parseConstraints($packageVersion->version());
                $versionConstraints = $parser->parseConstraints($version);

                if ($packageConstraints->getLowerBound()->compareTo($versionConstraints->getUpperBound(), '>')) {
                    $context->service(TaskReportPublisher::class)->publish(
                        Report::warn(sprintf(
                            'Existing package "%s" version "%s" is greater than target package version "%s"',
                            $name,
                            $packageVersion->__toString(),
                            $version
                        ))
                    );
                    return false;
                }
                if ($packageConstraints->getUpperBound()->compareTo($versionConstraints->getLowerBound(), '>')) {
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
            }

            if ($packageVersion->equalTo($version)) {
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
