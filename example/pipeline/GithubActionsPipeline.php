<?php

namespace Maestro2\Examples\Pipeline;

use Maestro2\Core\Config\RepositoryNode;
use Maestro2\Core\Task\CatTask;
use Maestro2\Core\Task\ClosureTask;
use Maestro2\Core\Task\FileTask;
use Maestro2\Core\Task\GitCommitTask;
use Maestro2\Core\Task\GitDiffTask;
use Maestro2\Core\Task\ProcessTask;
use Maestro2\Core\Task\SequentialTask;
use Maestro2\Core\Task\Task;
use Maestro2\Core\Task\TemplateTask;
use Maestro2\Core\Task\YamlTask;

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
                    'jobs' => $repository->vars()->get('jobs')
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
                args: ['git', 'checkout', '-b', 'github-actions'],
            ),
            new ProcessTask(
                args: ['git', 'push', 'origin', 'HEAD', '--force'],
            )
        ]);

    }
}
