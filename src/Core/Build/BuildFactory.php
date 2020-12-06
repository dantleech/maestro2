<?php

namespace Maestro2\Core\Build;

use Maestro2\Core\Config\MainNode;
use Maestro2\Core\Config\RepositoryNode;
use Maestro2\Core\Exception\RuntimeException;
use Maestro2\Core\Pipeline\RepositoryPipeline;
use Maestro2\Core\Pipeline\Repository\NullRepositoryPipeline;
use Maestro2\Core\Queue\Enqueuer;
use Maestro2\Core\Queue\Worker;
use Maestro2\Core\Task\CommandsTask;
use Maestro2\Core\Task\FileTask;
use Maestro2\Core\Task\GitRepositoryTask;
use Maestro2\Core\Task\HandlerFactory;
use Maestro2\Core\Task\NullTask;
use Maestro2\Core\Task\ProcessTask;
use Maestro2\Core\Task\SequentialTask;

class BuildFactory
{
    public function __construct(private Enqueuer $queue, private Worker $worker)
    {
    }



    public function createBuild(MainNode $config): Build
    {
        $tasks = [];

        $tasks[] = new FileTask(

            type: 'directory',

            path: $config->workspacePath(),
            exists: false,
        );

        $tasks[] = new FileTask(
            type: 'directory',
            path: $config->workspacePath(),
            mode: 0777,
            exists: true,
        );

        foreach ($config->repositories() as $repository) {
            $cwd = sprintf('%s/%s', $config->workspacePath(), $repository->name());
            $tasks[] = new SequentialTask([
                new GitRepositoryTask(
                    url: $repository->url(),
                    path: $cwd,
                ),
                $this->resolvePipeline($repository)->build($repository)
            ]);
        }

        return new Build($this->queue, $tasks, $this->worker);
    }

    private function resolvePipeline(RepositoryNode $repository): RepositoryPipeline
    {
        return (function (?string $pipeline): RepositoryPipeline {
            if (null === $pipeline) {
                return new NullRepositoryPipeline();
            }

            $pipeline = str_replace('.', '\\', $pipeline);

            if (!class_exists($pipeline)) {
                throw new RuntimeException(sprintf(
                    'Pipeline class "%s" cannot be found',
                    $pipeline
                ));
            }
            $pipeline = new $pipeline;
            if (!$pipeline instanceof RepositoryPipeline) {
                throw new RuntimeException(sprintf(
                    'Class "%s" is not a repository pipeline (implementing %s)',
                    get_class($pipeline),
                    RepositoryPipeline::class
                ));
            }

            return $pipeline;
        })($repository->pipeline());
    }
}
