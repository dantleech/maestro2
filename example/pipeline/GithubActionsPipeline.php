<?php

namespace Maestro2\Examples\Pipeline;

use Maestro2\Core\Config\RepositoryNode;
use Maestro2\Core\Pipeline\RepositoryPipeline;
use Maestro2\Core\Task\FileTask;
use Maestro2\Core\Task\JsonMergeTask;
use Maestro2\Core\Task\NullTask;
use Maestro2\Core\Task\SequentialTask;
use Maestro2\Core\Task\Task;
use Maestro2\Core\Task\TemplateTask;

class GithubActionsPipeline implements RepositoryPipeline
{
    public function build(RepositoryNode $repository): Task
    {
        return new SequentialTask([
            (in_array('phpbench', $repository->vars()->get('jobs')) ? new FileTask(
                type: 'file',
                path: $repository->path('.github/phpbench_regression.sh'),
                content: file_get_contents(__DIR__ . '/script/phpbench_regression.sh'),
                mode: 0755
            ): new NullTask()),
            new TemplateTask(
                group: $repository->name(),
                template: 'example/pipeline/template/ci.yml.twig',
                target: $repository->path('.github/workflows/ci.yml'),
                overwrite: true,
                vars: [
                    'name' => $repository->name(),
                    'url' => $repository->url(),
                    'jobs' => $repository->vars()->get('jobs'),
                    'phpVersions' => $repository->vars()->get('phpVersions'),
                    'phpstanArgs' => $repository->vars()->get('phpstanArgs'),
                    'dependencies' => $repository->vars()->get('dependencies'),
                    'checkoutParams' => [
                        // fetch all branches and tags - we need to get the
                        // branch if a dependency is depends on the dev-alias.
                        'fetch-depth' => 0,
                    ]
                ]
            ),
        ]);
    }
}
