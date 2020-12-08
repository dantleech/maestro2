<?php

namespace Maestro2\Core\Report;

final class Report
{
    const LEVEL_OK = 'ok';
    const LEVEL_WARN = 'warn';
    const LEVEL_FAIL = 'fail';

    private function __construct(private string $level, private string $title, private ?string $body = null)
    {
    }

    public static function ok(string $title, ?string $body = null): self
    {
        return new self(self::LEVEL_OK, $title, $body);
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

    public function level(): string
    {
        return $this->level;
    }

    public function title(): string
    {
        return $this->title;
    }
}
