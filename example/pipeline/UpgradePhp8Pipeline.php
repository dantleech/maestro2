<?php

namespace Maestro2\Examples\Pipeline;

use Maestro2\Core\Config\RepositoryNode;
use Maestro2\Core\Pipeline\RepositoryPipeline;
use Maestro2\Core\Task\JsonMergeTask;
use Maestro2\Core\Task\SequentialTask;
use Maestro2\Core\Task\Task;

class UpgradePhp8Pipeline implements RepositoryPipeline
{
    public function build(RepositoryNode $repository): Task
    {
        return new SequentialTask([
            new JsonMergeTask(
                path: $repository->path('composer.json'),
                data: [
                    'require' => [
                        'php' => '^7.3 || ^8.0',
                    ],
                ]
            ),
        ]);
    }
}
