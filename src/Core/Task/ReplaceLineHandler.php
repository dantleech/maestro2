<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Amp\Success;
use Maestro2\Core\Report\Report;
use Maestro2\Core\Report\ReportPublisher;
use Maestro2\Core\Task\Exception\TaskError;

class ReplaceLineHandler implements Handler
{
    private const NEWLINE_PATTERN = '\\r\\n|\\n|\\r';

    public function __construct(private ReportPublisher $publisher)
    {
    }

    public function taskFqn(): string
    {
        return ReplaceLineTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof ReplaceLineTask);
        if (!file_exists($task->path())) {
            throw new TaskError(sprintf(
                'File "%s" does not exist',
                $task->path()
            ));
        }

        $before = file_get_contents($task->path());
        $after = implode('', array_map(function (string $lineOrDelim) use ($task): string  {
            if (preg_match($task->regexp(), $lineOrDelim)) {
                return $task->line();
            }

            return $lineOrDelim;
        }, array_filter((array)preg_split('{(' . self::NEWLINE_PATTERN . ')}', $before, -1, PREG_SPLIT_DELIM_CAPTURE))));

        if ($before !== $after) {
            file_put_contents($task->path(), $after);
            $this->publisher->publish($task->group(), Report::ok(sprintf('Replaced line(s) in "%s"', $task->path())));
        }

        return new Success();
    }
}
