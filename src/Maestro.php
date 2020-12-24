<?php

namespace Maestro;

use Amp\Loop;
use Maestro\Core\Inventory\InventoryLoader;
use Maestro\Core\Inventory\MainNode;
use Maestro\Core\Exception\RuntimeException;
use Maestro\Core\Pipeline\NullPipeline;
use Maestro\Core\Pipeline\Pipeline;
use Maestro\Core\Queue\Enqueuer;
use Maestro\Core\Queue\Worker;
use Maestro\Core\Task\ContextFactory;
use Maestro\Core\Task\TaskContext;
use Maestro\Util\ClassNameFromFile;
use Throwable;
use function Amp\call;

class Maestro
{
    public function __construct(
        private InventoryLoader $loader,
        private Worker $worker,
        private Enqueuer $enqueuer,
        private ContextFactory $contextFactory
    ) {
    }

    public function run(
        string $pipeline,
        ?array $repos = []
    ): void {
        $pipeline = $this->resolvePipeline($pipeline);

        Loop::run(function () use ($pipeline, $repos) {
            yield call(function (MainNode $inventory) use ($pipeline, $repos) {
                $pollId = Loop::repeat(1000, function () {
                    $this->worker->updateStatus();
                });
                try {
                    $this->enqueuer->enqueue(
                        TaskContext::create(
                            $pipeline->build(
                                $repos ? $inventory->withSelectedRepos($repos) : $inventory
                            ),
                            $this->contextFactory->createContext()
                        )
                    );
                    yield $this->worker->start();
                } catch (Throwable $e) {
                    throw $e;
                } finally {
                    Loop::cancel($pollId);
                }
            }, $this->loader->load());
        });
    }

    private function resolvePipeline(?string $pipeline): Pipeline
    {
        if (null === $pipeline) {
            return new NullPipeline();
        }

        $pipelineClass = $this->resolveClass($pipeline);

        if (!class_exists($pipelineClass)) {
            throw new RuntimeException(sprintf(
                'Stage class "%s" cannot be found',
                $pipeline
            ));
        }

        try {
            $pipelineInstance = new $pipelineClass();
        } catch (Throwable $error) {
            throw new RuntimeException(sprintf(
                'Could not instantiate pipeline class "%s": %s',
                $pipeline,
                $error->getMessage()
            ), 0, $error);
        }

        if (!$pipelineInstance instanceof Pipeline) {
            throw new RuntimeException(sprintf(
                'Class "%s" is not a Pipeline (implementing %s)',
                get_class($pipelineInstance),
                Pipeline::class
            ));
        }

        return $pipelineInstance;
    }

    private function resolveClass(string $pipeline): string
    {
        if (!file_exists($pipeline)) {
            throw new RuntimeException(sprintf(
                'Pipeline file "%s" does not exist',
                $pipeline
            ));
        }

        return ClassNameFromFile::classNameFromFile($pipeline) ?: throw new RuntimeException(sprintf(
            'Could not find pipeline class in file "%s"',
            $pipeline
        ));
    }
}
