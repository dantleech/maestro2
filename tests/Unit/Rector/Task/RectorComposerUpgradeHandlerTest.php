<?php

namespace Maestro2\Tests\Unit\Rector\Task;

use Maestro2\Core\Queue\Queue;
use Maestro2\Core\Task\Handler;
use Maestro2\Rector\Task\RectorComposerUpgradeHandler;
use Maestro2\Rector\Task\RectorComposerUpgradeTask;
use Maestro2\Tests\Unit\Core\Task\HandlerTestCase;
use Maestro2\Tests\Util\ProcessUtil;

class RectorComposerUpgradeHandlerTest extends HandlerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        ProcessUtil::mustRun($this->workspace()->path(), 'git init');
        $this->workspace()->put('composer.json', json_encode([
            'name' => 'foobar/barfoo',
            'autoload' => [
                'Foobar\\' => 'src/',
            ]
        ]));
    }

    protected function createHandler(): Handler
    {
        return new RectorComposerUpgradeHandler(
            $this->container()->get(Queue::class)
        );
    }

    public function testUpgrade(): void
    {
        $this->runTask(
            new RectorComposerUpgradeTask(
                phpBin: 'php7.3',
                repoPath: $this->workspace()->path(),
            )
        );
    }
}
