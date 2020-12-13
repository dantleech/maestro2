<?php

namespace Maestro2;

use Amp\Promise;
use Maestro2\Core\Config\MainNode;
use Maestro2\Core\Exception\RuntimeException;
use Maestro2\Core\Pipeline\NullPipeline;
use Maestro2\Core\Pipeline\Pipeline;
use Maestro2\Core\Queue\Enqueuer;
use Maestro2\Core\Queue\Worker;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\TaskContext;
use Maestro2\Util\ClassNameFromFile;
use Throwable;
use function Amp\call;

class Maestro
{
    public function __construct(
        private MainNode $config,
        private Worker $worker,
        private Enqueuer $enqueuer
    ) {
    }

    public function run(
        string $pipeline,
        ?array $repos = []
    ): Promise {
        return call(function () use ($pipeline, $repos) {
            $promise = $this->enqueuer->enqueue(
                TaskContext::create(
                    $this->resolvePipeline($pipeline)->build(
                        $repos ? $this->config->withSelectedRepos($repos) : $this->config
                    ),
                    Context::create([], [
                        $this->config->php()
                    ])
                )
            );

            yield $this->worker->start();
            yield $promise;
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
            $pipelineInstance = new $pipelineClass;
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
