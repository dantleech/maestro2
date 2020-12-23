<?php

namespace Maestro\Composer;

use Amp\Promise;
use Maestro\Composer\Task\ComposerTask;
use Maestro\Core\Process\ProcessResult;
use Maestro\Core\Queue\Enqueuer;
use Maestro\Core\Task\Context;
use Maestro\Core\Task\PhpProcessTask;
use Maestro\Core\Task\TaskContext;
use Symfony\Component\Process\ExecutableFinder;
use function Amp\call;

class ComposerRunner
{
    /**
     * @var string
     */
    private $bin;

    public function __construct(private ComposerTask $task, private Context $context, private Enqueuer $enqueuer)
    {
        $this->bin = $this->resolveBin($task);
    }

    /**
     * @return Promise<ProcessResult>
     */
    public function run(array $args): Promise
    {
        return call(fn () => (yield $this->enqueuer->enqueue(TaskContext::create(new PhpProcessTask(
            cmd: array_values(array_merge([
                $this->bin
            ], $args))
        ), $this->context)))->result());
    }

    private function resolveBin(ComposerTask $task): string
    {
        return $task->composerBin() ?: (new ExecutableFinder())->find('composer') ?: 'composer';
    }
}
