<?php

namespace Maestro\Tests\Unit\Rector\Task;

use Maestro\Rector\Task\RectorComposerUpgradeTask;
use Maestro\Tests\Unit\Core\Task\HandlerTestCase;
use Maestro\Tests\Util\ProcessUtil;

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
