<?php

namespace Maestro2\Rector\Task;

use Amp\Promise;
use Maestro2\Composer\ComposerJson;
use Maestro2\Core\Queue\Enqueuer;
use Maestro2\Core\Task\ComposerTask;
use Maestro2\Core\Task\FileTask;
use Maestro2\Core\Task\GitCommitTask;
use Maestro2\Core\Task\Handler;
use Maestro2\Core\Task\ProcessTask;
use Maestro2\Core\Task\SequentialTask;
use Maestro2\Core\Task\Task;
use Maestro2\Core\Task\TemplateTask;
use function Amp\call;

class RectorComposerUpgradeHandler implements Handler
{
    private Enqueuer $enqueuer;

    public function __construct(Enqueuer $enqueuer)
    {
        $this->enqueuer = $enqueuer;
    }

    public function taskFqn(): string
    {
        return RectorComposerUpgradeTask::class;
    }

    public function run(Task $task, Facts $facts): Promise
    {
        assert($task instanceof RectorComposerUpgradeTask);

        return call(
            function (
                array $autoloadPaths,
                string $phpBin,
                string $repoPath,
            ) use ($task) {
                return yield $this->enqueuer->enqueue(new SequentialTask([
                    new TemplateTask(
                        template: __DIR__ . '/template/rector.php.twig',
                        target: $this->rectorConfigPath($task),
                        vars: [
                            'sets' => [
                                'vendor/rector/rector/config/set/phpunit70.php',
                                'vendor/rector/rector/config/set/phpunit80.php',
                                'vendor/rector/rector/config/set/phpunit90.php',
                            ]
                        ]
                    ),
                    new ComposerTask(
                        dev: true,
                        update: true,
                    ),
                    new ComposerTask(
                        dev: true,
                        remove: [
                            'phpstan/phpstan',
                            'symfony/filesystem',
                        ],
                        require: [
                            'rector/rector' => $task->rectorVersion(),
                        ],
                        update: true,
                    ),
                    new SequentialTask(array_map(fn (string $path) => new ProcessTask(
                        args: [ $task->phpBin(), 'vendor/bin/rector', 'process', $path ],
                    ), $autoloadPaths)),
                    new ComposerTask(
                        remove: ['rector/rector'],
                        update: true,
                    ),
                    new GitCommitTask(
                        paths: $autoloadPaths,
                        message: 'Automated Rector upgrade by Maestro2',
                    ),
                ]));
            },
            $facts->get(ComposerFacts::class)->autoloadPaths(),
            $facts->get(PhpRuntimeFacts::class)->phpBin(),
            $facts->get(Cwd::class)->path(),
        );
    }

    private function rectorConfigPath(RectorComposerUpgradeTask $task): string
    {
        return $task->repoPath() . '/rector.php';
    }
}
