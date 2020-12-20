<?php

namespace Maestro\Tests\EndToEnd;

use Maestro\Tests\IntegrationTestCase;
use Symfony\Component\Process\Process;

class RunTest extends IntegrationTestCase
{
    public function testNoPipelineSuggestsToCreateAPipeline(): void
    {
        $process = $this->maestro('run');
        self::assertFailure($process, 1);
        self::assertStringContainsString('for example', $process->getOutput());
        self::assertStringContainsString('MyPipeline', $process->getOutput());
    }

    public function testNotExistingPipelineShowsErrorMessage(): void
    {
        $process = $this->maestro('run pipeline/Foobar.php');
        self::assertFailure($process, 1);
    }

    public function testRunEmptyPipeline(): void
    {
        $process = $this->maestro(sprintf('run %s', $this->exampleEmptyPipeline()));
        self::assertSuccess($process);
    }

    public function testSetReportLevel(): void
    {
        $process = $this->maestro(sprintf('run %s --report-level=warn', $this->exampleEmptyPipeline()));
        self::assertSuccess($process);
    }

    public function testFilterByRepository(): void
    {
        $process = $this->maestro(sprintf('run %s --repo=barfoo', $this->exampleEmptyPipeline()));
        self::assertSuccess($process);
    }

    protected function maestro(string $cmd): Process
    {
        $process = Process::fromShellCommandline(sprintf('%s %s', __DIR__ . '/../../bin/maestro', $cmd));
        $process->run();
        return $process;
    }

    private static function assertSuccess(Process $process)
    {
        self::assertEquals(0, $process->getExitCode(), $process->getErrorOutput() .' : ' . $process->getOutput());
    }

    private static function assertFailure(Process $process, $exitCode = 127)
    {
        self::assertEquals($exitCode, $process->getExitCode(), 'Exit code correct');
    }

    private function exampleEmptyPipeline(): string
    {
        return __DIR__ . '/../../example/pipeline/EmptyPipeline.php';
    }
}
