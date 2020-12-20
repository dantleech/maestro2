<?php

namespace Maestro\Examples\Pipeline;

use Maestro\Core\Inventory\MainNode;
use Maestro\Core\Inventory\RepositoryNode;
use Maestro\Core\Pipeline\Pipeline;
use Maestro\Core\Task\GitSurveyTask;
use Maestro\Core\Task\JsonApiSurveyTask;
use Maestro\Core\Task\NullTask;
use Maestro\Core\Task\ParallelTask;
use Maestro\Core\Task\Task;
use function base64_encode;

class SurveyPipeline extends BasePipeline
{
    protected function buildRepository(RepositoryNode $repository): Task
    {
        return new ParallelTask([
            new GitSurveyTask(),
            new JsonApiSurveyTask(
                url: sprintf(
                    'https://api.github.com/repos/%s/actions/runs?branch=%s',
                    'phpactor/' . $repository->name(),
                    $repository->vars()->get('defaultBranch')
                ),
                headers: [
                    'Accept' => 'application/vnd.github.v3+json',
                    'Authorization' => sprintf('Basic %s', base64_encode(sprintf(
                        '%s:%s',
                        $repository->vars()->get('secret.githubUsername'),
                        $repository->vars()->get('secret.githubAuthToken'),
                    )))
                ],
                extract: function (array $data) {
                    $run = $data['workflow_runs'][0] ?? [];

                    if ([] === $run) {
                        return [
                            'gha #' => 'n/a',
                        ];
                    }

                    return [
                        'gha.#' => $run['run_number'],
                        'gha.sta' => $run['status'],
                        'gha.con' => $run['conclusion'],
                    ];
                }
            ),
        ]);
    }
}
