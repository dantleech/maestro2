<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\Report\Report;
use Maestro\Core\Report\TaskReportPublisher;
use Maestro\Core\Task\LineInFileTask;

class LineInFileHandler implements Handler
{
    private const NEWLINE_PATTERN = '\\r\\n|\\n|\\r';

    public function taskFqn(): string
    {
        return LineInFileTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof LineInFileTask);
        $this->runReplaceLine(
            $task,
            $context->service(Filesystem::class),
            $context->service(TaskReportPublisher::class),
        );
        
        return new Success($context);
    }

    private function runReplaceLine(LineInFileTask $task, Filesystem $filesystem, TaskReportPublisher $publisher): void
    {
        if (!$filesystem->exists($task->path())) {
            $publisher->publish(Report::warn(sprintf(
                '%s - file does not exist',
                $task->__toString()
            )));
            return;
        }
        
        $before = $filesystem->getContents($task->path());

        $found = false;
        $after = array_map(function (string $lineOrDelim) use ($task, &$found): string {
            if ($this->isMatch($task, $lineOrDelim)) {
                $found = true;
                return $task->line();
            }
        
            return $lineOrDelim;
        }, array_filter((array)preg_split(
            '{(' . self::NEWLINE_PATTERN . ')}',
            $before,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        )));

        if (false === $found && $task->append()) {
            $lastLineOffset = array_key_last($after);
            if ($lastLineOffset && $after[$lastLineOffset] !== "\n") {
                $after[] = "\n";
            }
            $after[] = $task->line();
        }

        $after = implode('', $after);
        
        if ($before !== $after) {
            $filesystem->putContents($task->path(), $after);
            $publisher->publish(
                Report::ok(sprintf('Replaced line(s) in "%s"', $task->path()))
            );
        }
    }

    private function isMatch(LineInFileTask $task, string $lineOrDelim): bool
    {
        $regexp = $task->regexp();
        if ($regexp) {
            return (bool)preg_match($regexp, $lineOrDelim);
        }

        return $task->line() === $lineOrDelim;
    }
}
