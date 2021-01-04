<?php

namespace Maestro\Core\Task;

use Maestro\Core\Fact\Fact;
use Stringable;

/**
 * Establish a Fact
 *
 * Inject a Fact into the pipeline. The Fact can be accessed from the `Context`
 */
class FactTask implements Task, Stringable
{
    /**
     * @param Fact $fact The fact to inject
     */
    public function __construct(private Fact $fact)
    {
    }

    public function fact(): Fact
    {
        return $this->fact;
    }

    public function __toString(): string
    {
        return sprintf('Establshing fact "%s"', $this->fact::class);
    }
}
