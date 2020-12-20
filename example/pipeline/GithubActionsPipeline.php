<?php

namespace Maestro\Examples\Pipeline;

use Maestro\Core\Inventory\RepositoryNode;
use Maestro\Core\Task\CatTask;
use Maestro\Core\Task\ClosureTask;
use Maestro\Core\Task\FileTask;
use Maestro\Core\Task\GitCommitTask;
use Maestro\Core\Task\GitDiffTask;
use Maestro\Core\Task\ProcessTask;
use Maestro\Core\Task\SequentialTask;
use Maestro\Core\Task\Task;
use Maestro\Core\Task\TemplateTask;
use Maestro\Core\Task\YamlTask;

class GithubActionsPipeline extends BasePipeline
{
    protected function buildRepository(RepositoryNode $repository): Task
    {
        return new SequentialTask([
            new TemplateTask(
                template: 'github/workflow.yml.twig',
                target: '.github/workflows/ci.yml',
                vars: [
                    'name' => 'CI',
                    'repo' => $repository,
                    'jobs' => $repository->vars()->get('ci.jobs')
                ]
            ),
            new CatTask(
                path: '.github/workflows/ci.yml'
            ),
            new GitCommitTask(
                message: 'Maestro is adding Github Actions',
                paths: [
                    '.github/workflows/ci.yml'
                ],
            ),
            new GitDiffTask(),
            new ProcessTask(
                cmd: ['git', 'checkout', '-b', 'github-actions'],
            ),
            //new ProcessTask(
            //    cmd: ['git', 'push', 'origin', 'HEAD', '--force'],
            //)
        ]);

    }
}
