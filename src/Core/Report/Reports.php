<?php

namespace Maestro2\Core\Report;

use ArrayIterator;
use Iterator;
use IteratorAggregate;
use Countable;

class Reports implements IteratorAggregate, Countable
{
    private array $reports;

    public function __construct(Report ...$reports)
    {
        $this->reports = $reports;
    }

    public function warns(): self
    {
        return new self(...array_filter(
            $this->reports,
            fn (Report $report) => $report->level() === Report::LEVEL_WARN
        ));
    }

    public function fails(): self
    {
        return new self(...array_filter(
            $this->reports,
            fn (Report $report) => $report->level() === Report::LEVEL_FAIL
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
