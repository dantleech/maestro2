<?php

namespace Maestro2\Examples\Pipeline\upgrade;

use Maestro2\Core\Config\RepositoryNode;
use Maestro2\Core\Stage\RepositoryStage;
use Maestro2\Core\Task\CommandsTask;
use Maestro2\Core\Task\ComposerTask;
use Maestro2\Core\Task\FileTask;
use Maestro2\Core\Task\JsonMergeTask;
use Maestro2\Core\Task\SequentialTask;
use Maestro2\Core\Task\Task;
use Maestro2\Core\Task\YamlTask;
use stdClass;

class PhpUnit8UpgradePipeline implements RepositoryStage
{
    public function build(RepositoryNode $repository): Task
    {
        return new SequentialTask([
            new YamlTask(
                path: $repository->path('phpstan.neon'),
                filter: function (array $data) {
                    if (isset($data['includes'])) {
                        unset($data['includes']);
                    }
                    $data['parameters']['level'] = 7;
                    return $data;
                }
            ),
            new ComposerTask(
                path: $repository->path(),
                dev: true,
                update: true,
                require: array_merge([
                    'symfony/filesystem' => '^4.0||^5.0',
                    'phpstan/phpstan' => '^0.12.0',
                ], $repository->vars()->get('requireDev')),
                remove: [
                    'infection/infection'
                ]
            ),
        ]);
    }
}
