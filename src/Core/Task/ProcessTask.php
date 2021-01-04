<?php

namespace Maestro\Core\Task;

use Closure;
use Maestro\Core\Process\ProcessResult;
use Stringable;
use function Clue\Arguments\split;

/**
 * Run a process
 *
 * For example, create a pull request with `gh` (Github CLI tool):
 *
 * ```php:task
 * new ProcessTask(
 *     cmd: [
 *         'gh',
 *         'pr',
 *         'create',
 *         '--fill',
 *         '-t',
 *         'Some commit message'
 *     ],
 *     allowFailure: true
 * )
 * ```
 *
 * Alternatively you can use a string for the command:
 *
 * ```php:task
 * new ProcessTask(
 *     cmd: 'gh pr create --fill -t "some commit message"',
 *     allowFailure: true
 * )
 * ```
 *
 * You can specify a closure which can be executed afterwards
 *
 * ```php:task
 * new ProcessTask(
 *     cmd: 'gh pr create --fill -t "some commit message"',
 *     after: function (ProcessResult $result, Context $context) {
 *         // do something
 *     }
 * )
 * ```
 *
 * The process will be run in the current working directory of the `Filesystem`
 * service, which can be set with the `SetDirectoryTask`.
 */
class ProcessTask implements Task, Stringable
{
    /**
     * @var list<string>
     */
    private array $cmd;

    /**
     * @param list<string>|string $cmd Command to run
     * @param (Closure(ProcessResult, Context):Context)|null $after Closure to run after the process has finished
     * @param bool $allowFailure If failure should be tolerated
     */
    public function __construct(
        array|string $cmd,
        private ?string $group = null,
        private ?Closure $after = null,
        private bool $allowFailure = false
    ) {
        if (is_string($cmd)) {
            $cmd = array_values(split($cmd));
        }
        $this->cmd = $cmd;
    }

    /**
     * @return list<string>
     */
    public function cmd(): array
    {
        return $this->cmd;
    }

    public function group(): ?string
    {
        return $this->group;
    }

    public function __toString(): string
    {
        return sprintf(
            'Running process: %s',
            implode(' ', array_map('escapeshellarg', $this->cmd))
        );
    }

    /**
     * @return Closure(ProcessResult, Context): Context
     */
    public function after(): ?Closure
    {
        return $this->after;
    }

    public function allowFailure(): bool
    {
        return $this->allowFailure;
    }
}
