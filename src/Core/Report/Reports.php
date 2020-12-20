<?php

namespace Maestro2\Core\Report;

use ArrayIterator;
use Iterator;
use IteratorAggregate;
use Countable;
use Maestro2\Core\Exception\RuntimeException;

/**
 * @implements IteratorAggregate<Report>
 */
class Reports implements IteratorAggregate, Countable
{
    /**
     * @var list<Report> $reports
     */
    public function __construct(private array $reports)
    {
    }

    public function forLevel(string $level): self
    {
        return new self(array_filter(
            $this->reports,
            fn (Report $report) => match ($level) {
                Report::LEVEL_OK => true,
                Report::LEVEL_INFO => in_array($report->level(), [ Report::LEVEL_INFO, Report::LEVEL_WARN, Report::LEVEL_FAIL ]),
                Report::LEVEL_WARN => in_array($report->level(), [ Report::LEVEL_WARN, Report::LEVEL_FAIL ]),
                Report::LEVEL_FAIL => $report->level() === Report::LEVEL_FAIL,
                default => throw new RuntimeException(sprintf(
                    'Report level "%s" invalid, must be one of: "%s"',
                    $level,
                    implode('", "', [ Report::LEVEL_INFO, Report::LEVEL_WARN, Report::LEVEL_FAIL ])
                ))
            }
        ));
    }

    public function matchingTitle(string $pattern): self
    {
        return new self(array_filter(
            $this->reports,
            fn (Report $report) => preg_match('{' . $pattern . '}', $report->title())
        ));
    }

    public function warns(): self
    {
        return new self(array_filter(
            $this->reports,
            fn (Report $report) => $report->level() === Report::LEVEL_WARN
        ));
    }

    public function fails(): self
    {
        return new self(array_filter(
            $this->reports,
            fn (Report $report) => $report->level() === Report::LEVEL_FAIL
        ));
    }

    public function infos(): self
    {
        return new self(array_filter(
            $this->reports,
            fn (Report $report) => $report->level() === Report::LEVEL_INFO
        ));
    }

    public function oks(): self
    {
        return new self(array_filter(
            $this->reports,
            fn (Report $report) => $report->level() === Report::LEVEL_OK
        ));
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->reports);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->reports);
    }
}
