<?php

namespace Maestro2\Examples\Pipeline;

use Maestro2\Core\Config\RepositoryNode;
use Maestro2\Core\Process\ProcessResult;
use Maestro2\Core\Task\ComposerTask;
use Maestro2\Core\Task\ConditionalTask;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\GitCommitTask;
use Maestro2\Core\Task\GitDiffTask;
use Maestro2\Core\Task\ProcessTask;
use Maestro2\Core\Task\SequentialTask;
use Maestro2\Core\Task\Task;
use Maestro2\Core\Task\YamlTask;

class UpgradePhpStanPipeline extends BasePipeline
{
    const VERSION = '~0.12.0';

    protected function buildRepository(RepositoryNode $repository): Task
    {
        return new SequentialTask([
            new YamlTask(
                inline: 3,
                path: 'phpstan.neon',
                filter: function (array $data) {
                    return $this->processConfig($data);
                },
            ),
            new ComposerTask(
                require: [
                    'phpstan/phpstan' => self::VERSION,
                ],
                dev: true,
                update: true,
            ),
            $this->phpstanTask($repository, true),
            new ConditionalTask(
                predicate: function (Context $context) {
                    return $context->var('phpstan-exit') !== 0;
                },
                task: $this->generateBaselineTask($repository),
            ),
            new GitDiffTask(),
            new GitCommitTask(
                paths: [
                    'composer.json',
                    'phpstan.neon',
                ],
                message: sprintf('Maestro updates PHPStan to version %s', self::VERSION)
            ),
        ]);
    }

    private function processConfig(array $config): array
    {
        $config['parameters']['level'] = 7;
        if (isset($config['includes'])) {
            $config = $this->processIncludes($config);
        }

        $config['parameters']['paths'] = ['lib'];
        $config['parameters']['checkMissingIterableValueType'] = false;

        return $config;
    }

    private function processIncludes(array $config): array
    {
        $keep = [];
        foreach ($config['includes'] as $include) {
            if (!preg_match('{config.level([0-9])}', $include, $matches)) {
                $keep[] = $include;
                continue;
            }

            $config['parameters']['level'] = $matches[1];
            break;
        }

        $config['includes'] = $keep;

        return $config;
    }

    private function phpstanTask(RepositoryNode $repository, bool $allowFailure = false)
    {
        return new ProcessTask(
            args: [
                $repository->main()->php()->phpBin(),
                './vendor/bin/phpstan',
                '--no-interaction',
                'analyse',
            ],
            allowFailure: $allowFailure,
            after: function (ProcessResult $result, Context $context) {
                return $context->withVar('phpstan-exit', $result->exitCode());
            },
        );
    }

    private function generateBaselineTask(RepositoryNode $repository): Task
    {
        return new SequentialTask([
            new ProcessTask(
                args: [
                    $repository->main()->php()->phpBin(),
                    './vendor/bin/phpstan',
                    'analyse',
                    '--generate-baseline'
                ]
            ),
            new YamlTask(
                inline: 3,
                data: [
                    'includes' => [
                    ],
                ],
                path: 'phpstan.neon',
                filter: function (array $data) {
                    foreach ($data['includes'] as $include) {
                        if ($include === 'phpstan-baseline.neon') {
                            return;
                        }
                    }

                    $data['includes'][] = 'phpstan-baseline.neon';
                    return $data;
                },
            ),
            $this->phpstanTask($repository),
            new GitCommitTask(
                paths: [
                    'phpstan-baseline.neon',
                ],
                message: sprintf('Maestro adds PHPStan baseline')
            ),
        ]);
    }
}
