<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Amp\Success;
use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Fact\GroupFact;
use Maestro2\Core\Filesystem\Filesystem;
use Maestro2\Core\Report\Report;
use Maestro2\Core\Report\ReportPublisher;
use Maestro2\Core\Task\Exception\TaskError;

class ReplaceLineHandler implements Handler
{
    private const NEWLINE_PATTERN = '\\r\\n|\\n|\\r';

    public function __construct(private Filesystem $filesystem, private ReportPublisher $publisher)
    {
    }

    public function taskFqn(): string
    {
        return ReplaceLineTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof ReplaceLineTask);
        $this->runReplaceLine(
            $task,
            $this->filesystem->cd(
                $context->factOrNull(CwdFact::class)?->cwd() ?: '/'
            ),
            $context->factOrNull(GroupFact::class)?->group() ?: 'replace-line'
        );
        
        return new Success($context);
    }

    private function runReplaceLine(ReplaceLineTask $task, Filesystem $filesystem, string $group): void
    {
        if (!$filesystem->exists($task->path())) {
            throw new TaskError(sprintf(
                'File "%s" does not exist',
                $task->path()
            ));
        }
        
        $before = $filesystem->getContents($task->path());
        $after = implode('', array_map(function (string $lineOrDelim) use ($task): string {
            if (preg_match($task->regexp(), $lineOrDelim)) {
                return $task->line();
            }
        
            return $lineOrDelim;
        }, array_filter((array)preg_split(
            '{(' . self::NEWLINE_PATTERN . ')}',
            $before,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        ))));
        
        if ($before !== $after) {
            $filesystem->putContents($task->path(), $after);
            $this->publisher->publish(
                $group,
                Report::ok(sprintf('Replaced line(s) in "%s"', $task->path()))
            );
        }
    }
}
