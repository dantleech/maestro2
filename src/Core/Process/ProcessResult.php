<?php

namespace Maestro2\Core\Process;

use function Clue\Arguments\split;

class ProcessResult
{
    /**
     * @var list<string>
     */
    private array $cmd;

    /**
     * @param list<string>|string $cmd
     */
    public function __construct(
        private int $exitCode,
        private string $stdOut,
        private string $stdErr,
        array|string $cmd,
        private string $cwd
    ) {
        if (is_string($cmd)) {
            $cmd = array_values(split($cmd));
        }

        $this->cmd = $cmd;
    }

    /**
     * @param list<string>|string $cmd
     */
    public static function ok(array|string $cmd, string $cwd, string $stdOut = '', string $stdErr = ''): self
    {
        return new self(0, $stdOut, $stdErr, $cmd, $cwd);
    }

    public static function new(array|string $cmd, string $cwd, int $exitCode, string $stdOut = '', string $stdErr = ''): self
    {
        return new self($exitCode, $stdOut, $stdErr, $cmd, $cwd);
    }

    public static function fail(array|string $cmd, string $cwd, int $exitCode = 127, string $stdOut = '', string $stdErr = ''): self
    {
        return new self($exitCode, $stdOut, $stdErr, $cmd, $cwd);
    }


    public function exitCode(): int
    {
        return $this->exitCode;
    }

    public function isOk(): bool
    {
        return $this->exitCode === 0;
    }

    public function stdOut(): string
    {
        return $this->stdOut;
    }

    public function stdErr(): string
    {
        return $this->stdErr;
    }

    public function cwd(): string
    {
        return $this->cwd;
    }

    public function cmd(): array
    {
        return $this->cmd;
    }
}
