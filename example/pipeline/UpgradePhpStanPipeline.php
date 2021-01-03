<?php

namespace Maestro\Examples\Pipeline;

use Maestro\Composer\Fact\ComposerFact;
use Maestro\Core\Inventory\RepositoryNode;
use Maestro\Core\Process\ProcessResult;
use Maestro\Composer\Task\ComposerTask;
use Maestro\Core\Task\ConditionalTask;
use Maestro\Core\Task\Context;
use Maestro\Core\Task\GitCommitTask;
use Maestro\Core\Task\GitDiffTask;
use Maestro\Core\Task\PhpProcessTask;
use Maestro\Core\Task\ProcessTask;
use Maestro\Core\Task\SequentialTask;
use Maestro\Core\Task\Task;
use Maestro\Core\Task\YamlTask;

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
            new ComposerTask(),
            new ConditionalTask(
                predicate: function (Context $context) {
                    if (false === $context->fact(ComposerFact::class)->packages()->has('phpstan/phpstan')) {
                        return false;
                    }

                    return $context->fact(ComposerFact::class)
                        ->packages()->get('phpstan/phpstan')
                        ->version()->lessThan(self::VERSION);
                },
                task: new SequentialTask([
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
                            'phpstan-baseline.neon',
                        ],
                        message: sprintf('Maestro updates PHPStan to version %s', self::VERSION)
                    ),
                ])
            )
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
        return new PhpProcessTask(
            cmd: [
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
            new PhpProcessTask(
                cmd: [
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
        ]);
    }
}
