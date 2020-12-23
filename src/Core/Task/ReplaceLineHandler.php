<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\Report\Report;
use Maestro\Core\Report\TaskReportPublisher;

class ReplaceLineHandler implements Handler
{
    private const NEWLINE_PATTERN = '\\r\\n|\\n|\\r';

    public function taskFqn(): string
    {
        return ReplaceLineTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof ReplaceLineTask);
        $this->runReplaceLine(
            $task,
            $context->service(Filesystem::class),
            $context->service(TaskReportPublisher::class),
        );
        
        return new Success($context);
    }

    private function runReplaceLine(ReplaceLineTask $task, Filesystem $filesystem, TaskReportPublisher $publisher): void
    {
        if (!$filesystem->exists($task->path())) {
            $publisher->publish(Report::warn(sprintf(
                '%s - file does not exist',
                $task->__toString()
            )));
            return;
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
            $publisher->publish(
                Report::ok(sprintf('Replaced line(s) in "%s"', $task->path()))
            );
        }
    }
}
