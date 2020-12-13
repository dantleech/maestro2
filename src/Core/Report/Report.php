<?php

namespace Maestro2\Core\Report;

final class Report
{
    public const LEVEL_INFO = 'info';
    public const LEVEL_OK = 'ok';
    public const LEVEL_WARN = 'warn';
    public const LEVEL_FAIL = 'fail';

    /**
     * @param self::LEVEL_INFO | self::LEVEL_OK | self::LEVEL_WARN | self::LEVEL_FAIL $level
     */
    private function __construct(private string $level, private string $title, private ?string $body = null)
    {
    }

    public static function ok(string $title, ?string $body = null): self
    {
        return new self(self::LEVEL_OK, $title, $body);
    }

    public static function info(string $title, ?string $body = null): self
    {
        return new self(self::LEVEL_INFO, $title, $body);
    }

    public static function warn(string $title, ?string $body = null): self
    {
        return new self(self::LEVEL_WARN, $title, $body);
    }

    public static function fail(string $title, ?string $body = null): self
    {
        return new self(self::LEVEL_FAIL, $title, $body);
    }

    public function body(): ?string
    {
        return $this->body;
    }

    /**
     * @return self::LEVEL_OK | self::LEVEL_WARN | self::LEVEL_FAIL
     */
    public function level(): string
    {
        return $this->level;
    }

    public function title(): string
    {
        return $this->title;
    }
}
