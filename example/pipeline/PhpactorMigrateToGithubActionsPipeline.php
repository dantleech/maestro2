<?php

namespace Maestro2\Examples\Pipeline;

use Maestro2\Core\Config\RepositoryNode;
use Maestro2\Core\Pipeline\RepositoryPipeline;
use Maestro2\Core\Task\CommandsTask;
use Maestro2\Core\Task\FileTask;
use Maestro2\Core\Task\GitRepositoryTask;
use Maestro2\Core\Task\JsonMergeTask;
use Maestro2\Core\Task\NullTask;
use Maestro2\Core\Task\ReplaceLineHandler;
use Maestro2\Core\Task\ReplaceLineTask;
use Maestro2\Core\Task\SequentialTask;
use Maestro2\Core\Task\Task;
use Maestro2\Core\Task\TemplateTask;
use stdClass;

class PhpactorMigrateToGithubActionsPipeline implements RepositoryPipeline
{
    public function build(RepositoryNode $repository): Task
    {
        return new SequentialTask([
            new FileTask(
                path: $repository->path('.travis.yml'),
                exists: false,
            ),
            (new GithubActionsPipeline())->build($repository),
            new ReplaceLineTask(
                group: $repository->name(),
                path: $repository->path('README.md'),
                regexp: '{Build Status.*travis}',
                line: sprintf('![CI](https://github.com/phpactor/%s/workflows/CI/badge.svg)', $repository->name()),
            ),
            new JsonMergeTask(
                path: $repository->path('composer.json'),
                data: [
                    'require-dev' => array_merge([
                        'phpunit/phpunit' => '^8.0',
                    ], $repository->vars()->get('requireDev'))
                ],
                filter: function (stdClass $object) {
                    if (isset($object->{"require-dev"}->{"infection/infection"})) {
                        unset($object->{"require-dev"}->{"infection/infection"});
                    }

                    return $object;
                }
            ),
            new CommandsTask(
                group: $repository->name(),
                commands: [
                    [ 'git',  'checkout',  '-b', 'githubactions' ],
                    [ 'git',  'rm',  '.travis.yml' ],
                    [ 'git',  'add',  'composer.json' ],
                    [ 'git',  'add',  'README.md' ],
                    [ 'git',  'add',  '.github' ],
                    [ 'git',  'commit',  '-m', 'Add github actions' ],
                    [ 'git',  'push',  'origin', 'HEAD', '-f' ],
                ],
                cwd: $repository->path(),
                failFast: true
            ),
        ]);
    }
}
