<?php

namespace Maestro\Core\Task;

use Closure;
use Maestro\Core\Process\ProcessResult;

/**
 * Run a PHP process
 *
 * Similar to the `ProcessTask` but will prefix the PHP binary to the executed
 * commands.
 *
 * ```php
 * new PhpProcess('./vendor/bin/phpunit')
 * ```
 *
 * Is equivilent to running
 *
 * ```
 * $ php ./vendor/bin/phpunit
 * ```
 *
 * By default the PHP binary used by Maestro will be used or you can lay a
 * `PhpFact` in the pipeline to specify an alternate PHP binary.
 */
class PhpProcessTask implements Task
{
    /**
     * @param list<string>|string $cmd Command to run
     * @param (Closure(ProcessResult, Context): Context)|null $after Closure to run afterwards, will be passed the proces result and the `Context`.
     * @param bool $allowFailure Ignore failures
     */
    public function __construct(
        private array|string $cmd,
        private ?Closure $after = null,
        private bool $allowFailure = false
    ) {
    }

    /**
     * @return (Closure(ProcessResult, Context): Context)|null
     */
    public function after(): ?Closure
    {
        return $this->after;
    }

    public function allowFailure(): bool
    {
        return $this->allowFailure;
    }

    /**
     * @return list<string>|string
     */
    public function cmd(): array|string
    {
        return $this->cmd;
    }
}
