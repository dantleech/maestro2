<?php

namespace Maestro\Tests\Unit\Core\Task;

use Maestro\Core\Task\ClosureTask;
use Maestro\Core\Task\Context;
use Maestro\Core\Task\MapTask;
use PHPUnit\Framework\TestCase;

class MapHandlerTest extends HandlerTestCase
{
    public function testMapsValuesToFactory(): void
    {
        $context = $this->runTask(
            new MapTask(
                fn (string $val) => new ClosureTask(
                    fn (Context $context) => $context->withVar($val, $val)
                ),
                ['one', 'two']
            )

        );

        self::assertEquals('one', $context->var('one'));
    }
}
