<?php

namespace Maestro\Core\Task;

use Closure;
use Stringable;
use stdClass;

/**
 * Modify a JSON document
 *
 * Merge data into a JSON document or apply a filter.
 *
 * ```php
 * new JsonMergeTask(
 *     path: 'composer.json',
 *     data: [
 *         'minimum-stability' => 'dev',
 *     ]
 * );
 * ```
 *
 * You can also filter the object:
 *
 * ```php
 * new JsonMergeTask(
 *     path: 'composer.json',
 *     filter: function (stdClass $object) {
 *         unset($object->{'minimum-stability'});
 *         return $ibject;
 *     }
 * );
 * ```
 */
class JsonMergeTask implements Task, Stringable
{
    /**
     * @param Closure(stdClass):stdClass|null $filter
     */
    public function __construct(private string $path, private array $data = [], private ?Closure $filter = null)
    {
    }

    public function data(): array
    {
        return $this->data;
    }

    public function path(): string
    {
        return $this->path;
    }

    /**
     * @return Closure(stdClass $exitingData):stdClass|null
     */
    public function filter(): ?Closure
    {
        return $this->filter;
    }

    public function __toString(): string
    {
        return sprintf('treating JSON file "%s"', $this->path);
    }
}
