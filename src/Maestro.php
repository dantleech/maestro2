<?php

namespace Maestro2;

use Amp\Promise;
use Amp\Success;
use Maestro2\Core\Build\BuildFactory;
use Maestro2\Core\Config\ConfigLoader;
use Maestro2\Core\Config\MainNode;
use Maestro2\Core\Exception\RuntimeException;
use Maestro2\Core\Fact\PhpFact;
use Maestro2\Core\Pipeline\NullPipeline;
use Maestro2\Core\Pipeline\Pipeline;
use Maestro2\Core\Queue\Enqueuer;
use Maestro2\Core\Queue\Worker;
use Maestro2\Core\Stage\Stage\NullRepositoryStage;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\TaskContext;
use function Amp\call;

class Maestro
{
    public function __construct(
        private MainNode $config,
        private Worker $worker,
        private Enqueuer $enqueuer
    )
    {
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

        $pipeline = str_replace('.', '\\', $pipeline);

        if (!class_exists($pipeline)) {
            throw new RuntimeException(sprintf(
                'Stage class "%s" cannot be found',
                $pipeline
            ));
        }
        $pipeline = new $pipeline;
        if (!$pipeline instanceof Pipeline) {
            throw new RuntimeException(sprintf(
                'Class "%s" is not a Pipeline (implementing %s)',
                get_class($pipeline),
                Pipeline::class
            ));
        }

        return $pipeline;
    }
}
