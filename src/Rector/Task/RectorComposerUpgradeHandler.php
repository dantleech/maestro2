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

    public function run(Task $task): Promise
    {
        assert($task instanceof RectorComposerUpgradeTask);

        return call(function (ComposerJson $composerJson) use ($task) {
            return yield $this->enqueuer->enqueue(new SequentialTask([
                new TemplateTask(
                    template: __DIR__ . '/template/rector.php.twig',
                    target: $this->rectorConfigPath($task),
                    vars: [
                        'sets' => []
                    ]
                ),
                new ComposerTask(
                    path: $task->repoPath(),
                    require: ['rector/rector' => $task->rectorVersion()],
                    update: true,
                ),
                new SequentialTask(array_map(fn (string $path) => new ProcessTask(
                    args: [ $task->phpBin(), 'vendor/bin/rector', 'process', $path ],
                    cwd: $task->repoPath(),
                ), $composerJson->autoloadPaths())),
                new ComposerTask(
                    path: $task->repoPath(),
                    remove: ['rector/rector'],
                    update: true,
                ),
                new FileTask(
                    path: $this->rectorConfigPath($task),
                    exists: false
                ),
                new GitCommitTask(
                    paths: $composerJson->autoloadPaths(),
                    message: 'Automated Rector upgrade by Maestro2',
                    cwd: $task->repoPath(),
                ),
            ]));
        }, ComposerJson::fromProjectRoot($task->repoPath()));
    }

    private function rectorConfigPath(RectorComposerUpgradeTask $task): string
    {
        return $task->repoPath() . '/rector.php';
    }
}
