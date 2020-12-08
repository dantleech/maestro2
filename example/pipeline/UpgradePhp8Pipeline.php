<?php

namespace Maestro2\Examples\Pipeline;

use Maestro2\Core\Config\RepositoryNode;
use Maestro2\Core\Stage\RepositoryStage;
use Maestro2\Core\Task\JsonMergeTask;
use Maestro2\Core\Task\SequentialTask;
use Maestro2\Core\Task\StageTask;
use Maestro2\Core\Task\Task;
use Maestro2\Rector\Stage\ComposerUpgradeStage;
use Maestro2\Rector\Task\RectorComposerUpgradeTask;

class UpgradePhp8Pipeline implements RepositoryStage
{
    public function build(RepositoryNode $repository): Task
    {
        return new SequentialTask([
            (new 
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
