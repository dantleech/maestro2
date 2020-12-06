<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Exception\RuntimeException;
use Maestro2\Core\Task\Exception\TaskError;
use Maestro2\Core\Task\FileHandler;
use Maestro2\Core\Task\FileTask;
use Maestro2\Core\Task\HandlerFactory;
use Maestro2\Tests\IntegrationTestCase;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use function Amp\Promise\wait;

class FileHandlerTest extends IntegrationTestCase
{
    public function testCreatesDirectory(): void
    {
        $this->runTask(new FileTask(
            path: $this->workspace()->path('foobar/directory'),
            type: 'directory',
        ));
        self::assertFileExists($this->workspace()->path('foobar/directory'));
    }

    public function testIgnoresExistingDirectory(): void
    {
        $this->workspace()->mkdir('foobar/directory');
        $this->runTask(new FileTask(
            path: $this->workspace()->path('foobar/directory'),
            type: 'directory',
        ));
        self::assertFileExists($this->workspace()->path('foobar/directory'));
    }

    public function testRemovesDirectoryIfExistingIsFalse(): void
    {
        $this->workspace()->mkdir('foobar/directory');
        self::assertFileExists($this->workspace()->path('foobar/directory'));
        $this->runTask(new FileTask(
            path: $this->workspace()->path('foobar/directory'),
            type: 'directory',
            exists: false
        ));
        self::assertFileDoesNotExist($this->workspace()->path('foobar/directory'));
    }

    public function testExceptionWhenFirectoryIsAFile(): void
    {
        $this->expectException(TaskError::class);
        $this->expectExceptionMessage('to be a directory');
        $this->workspace()->put('foobar/directory', 'file!');
        self::assertFileExists($this->workspace()->path('foobar/directory'));
        $this->runTask(new FileTask(
            path: $this->workspace()->path('foobar/directory'),
            type: 'directory',
            exists: true
        ));
    }

    public function testCreatesFile(): void
    {
        $this->runTask(new FileTask(
            path: $this->workspace()->path('file'),
            type: 'file',
        ));

        self::assertFileExists($this->workspace()->path('file'));
    }

    public function testCreatesFileWithContent(): void
    {
        $this->runTask(new FileTask(
            path: $this->workspace()->path('file'),
            type: 'file',
            content: 'Hello World',
        ));

        self::assertFileExists($this->workspace()->path('file'));
        self::assertEquals('Hello World', $this->workspace()->getContents('file'));
    }

    public function testExceptionIfContentProvidedAndTypeIsDirectory(): void
    {
        $this->expectException(TaskError::class);
        $this->expectExceptionMessage('Content provided but file type is "directory"');
        $this->runTask(new FileTask(
            path: $this->workspace()->path('file'),
            type: 'directory',
            content: 'Hello World',
        ));
    }

    public function testCreatesFileParentDirectories(): void
    {
        $this->runTask(new FileTask(
            path: $this->workspace()->path('barfoo/foobar/file'),
            type: 'file',
        ));

        self::assertFileExists($this->workspace()->path('barfoo/foobar/file'));
    }

    private function runTask(FileTask $fileTask)
    {
        return wait((new HandlerFactory([
            new FileHandler(new NullLogger())
        ]))->handlerFor($fileTask)->run($fileTask));
    }
}
