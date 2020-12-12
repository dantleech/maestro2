<?php

namespace Maestro2;

use Amp\Promise;
use Amp\Success;
use Maestro2\Core\Build\BuildFactory;
use Maestro2\Core\Config\ConfigLoader;
use Maestro2\Core\Config\MainNode;
use Maestro2\Core\Exception\RuntimeException;
use Maestro2\Core\Pipeline\NullPipeline;
use Maestro2\Core\Pipeline\Pipeline;
use Maestro2\Core\Queue\Enqueuer;
use Maestro2\Core\Queue\Worker;
use Maestro2\Core\Stage\Stage\NullRepositoryStage;
use Maestro2\Core\Task\Context;
use function Amp\call;

class Maestro
{
    public function __construct(
        private Worker $worker,
        private Enqueuer $enqueuer
    )
    {
    }

    public function run(
        string $pipeline
    ): Promise {
        return call(function () use ($pipeline) {
            $promise = $this->enqueuer->enqueue($this->resolvePipeline($pipeline), Context::create());

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
