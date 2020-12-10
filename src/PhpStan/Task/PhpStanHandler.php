<?php

namespace Maestro2\PhpStan\Task;

use Amp\Promise;
use Maestro2\Core\Queue\Enqueuer;
use Maestro2\Core\Task\ComposerTask;
use Maestro2\Core\Task\Handler;
use Maestro2\Core\Task\SequentialTask;
use Maestro2\Core\Task\Task;
use Maestro2\Core\Task\TemplateTask;

class PhpStanHandler implements Handler
{
    private Enqueuer $enqueuer;
    private FactBook $book;


    public function __construct(Enqueuer $enqueuer, FactBook $book)
    {
        $this->enqueuer = $enqueuer;
        $this->book = $book;
    }

    public function taskFqn(): string
    {
        return PhpStanTask::class;
    }

    public function run(Task $task): Promise
    {
        assert($task instanceof PhpStanTask);
        return $this->enqueuer->enqueue(new SequentialTask([
            new TemplateTask(
                template: __DIR__ . '/template/phpstan.neon.twig',
                target: $task->repoPath() . '/phpstan.neon',
                overwrite: true,
                vars: [
                    'level' => $task->level(),
                    'paths' => $task->paths() ?: $this->book->get('composer.autoload_paths')
                ]
            ),
            new ComposerTask(
                phpBin: $task->phpBin(),
                path: $task->repoPath(),
                require: [
                    'phpstan/phpstan' => $task->phpstanVersion()
                ],
                update: true
            ),
        ]));
    }
}
