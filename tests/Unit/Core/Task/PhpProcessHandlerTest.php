<?php

namespace Maestro\Tests\Unit\Core\Task;

use Maestro\Core\Fact\PhpFact;
use Maestro\Core\Task\Context;
use Maestro\Core\Task\PhpProcessTask;
use Maestro\Core\Process\ProcessResult;
use PHPUnit\Framework\TestCase;

class PhpProcessHandlerTest extends HandlerTestCase
{
    public function testWithCmdString(): void
    {
        $this->processRunner()->expect(ProcessResult::ok('php3 foobar', '/'));
        $context = $this->runTask(new PhpProcessTask(
            cmd: 'foobar'
        ), $this->defaultContext()->merge(Context::create([], [
            new PhpFact('php3')
        ])));
        self::assertInstanceOf(Context::class, $context);
    }
}
