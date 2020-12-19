<?php

namespace Maestro2\Rector\Task;

use Amp\Promise;
use Maestro2\Composer\Fact\ComposerJsonFact;
use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Fact\PhpFact;
use Maestro2\Core\Queue\Enqueuer;
use Maestro2\Core\Task\ComposerTask;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\GitCommitTask;
use Maestro2\Core\Task\Handler;
use Maestro2\Core\Task\ProcessTask;
use Maestro2\Core\Task\SequentialTask;
use Maestro2\Core\Task\Task;
use Maestro2\Core\Task\TaskContext;
use Maestro2\Core\Task\TemplateTask;
use Maestro2\Rector\Fact\RectorInstallFact;
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

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof RectorComposerUpgradeTask);

        return call(
            function (
                array $autoloadPaths,
                string $phpBin,
                string $repoPath,
            ) use ($task, $context) {
                return yield $this->enqueuer->enqueue(TaskContext::create(
                    new SequentialTask([
                        new TemplateTask(
                            template: __DIR__ . '/template/rector.php.twig',
                            target: $this->rectorConfigPath($repoPath, $task),
                            vars: [
                                'sets' => [
                                    'vendor/rector/rector/config/set/phpunit70.php',
                                    'vendor/rector/rector/config/set/phpunit80.php',
                                    'vendor/rector/rector/config/set/phpunit90.php',
                                ]
                            ]
                        ),
                        new SequentialTask(array_map(fn (string $path) => new ProcessTask(
                            cmd: [
                                $context->fact(PhpFact::class)->phpBin(),
                                $context->fact(RectorInstallFact::class)->binPath(),
                                'process',
                                $path
                            ],
                        ), $autoloadPaths)),
                        new ComposerTask(
                            remove: ['rector/rector'],
                            update: true,
                        ),
                        new GitCommitTask(
                            paths: $autoloadPaths,
                            message: 'Automated Rector upgrade by Maestro2',
                        ),
                    ]),
                    $context
                ));
            },
            $context->fact(ComposerJsonFact::class)->autoloadPaths(),
            $context->fact(PhpFact::class)->phpBin(),
            $task->path() ?: $context->fact(CwdFact::class)->cwd()
        );
    }

    private function rectorConfigPath(string $cwd, RectorComposerUpgradeTask $task): string
    {
        return $cwd . '/rector.php';
    }
}
