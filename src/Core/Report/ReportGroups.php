<?php

namespace Maestro2\Core\Report;

use ArrayIterator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<ReportGroup>
 */
class ReportGroups implements IteratorAggregate
{
    /**
     * @param array<ReportGroup> $groups
     */
    public function __construct(private array $groups)
    {
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->groups);
    }

    public function reports(): Reports
    {
        return new Reports(array_reduce(
            $this->groups,
            function (array $initial, ReportGroup $group) {
                return array_merge(
                    $initial,
                    iterator_to_array($group->reports())
                );
            },
            []
        ));
    }
}
