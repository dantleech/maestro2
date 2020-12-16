<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Filesystem\Filesystem;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\Exception\TaskError;
use Maestro2\Core\Task\FileHandler;
use Maestro2\Core\Task\FileTask;
use Maestro2\Core\Task\Handler;
use Psr\Log\NullLogger;
use SplFileInfo;

class FileHandlerTest extends HandlerTestCase
{
    protected function defaultContext(): Context
    {
        return Context::create([], [
            new CwdFact('/')
        ]);
    }

    public function testCreatesDirectory(): void
    {
        $this->runTask(new FileTask(
            path: 'foobar/directory',
            type: 'directory',
        ));
        self::assertFileExists($this->workspace()->path('foobar/directory'));
    }

    public function testCreatesDirectoryInCwd(): void
    {
        $this->runTask(new FileTask(
            path: '../barfoo/directory',
            type: 'directory',
        ), Context::create([], [
            new CwdFact('foobar')
        ]));
        self::assertFileExists($this->workspace()->path('barfoo/directory'));
    }

    public function testIgnoresExistingDirectory(): void
    {
        $this->workspace()->mkdir('foobar/directory');
        $this->runTask(new FileTask(
            path: 'foobar/directory',
            type: 'directory',
        ));
        self::assertFileExists($this->workspace()->path('foobar/directory'));
    }

    public function testRemovesDirectoryIfExistingIsFalse(): void
    {
        $this->workspace()->mkdir('foobar/directory');
        self::assertFileExists($this->workspace()->path('foobar/directory'));
        $this->runTask(new FileTask(
            path: 'foobar/directory',
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
            path: 'foobar/directory',
            type: 'directory',
            exists: true
        ));
    }

    public function testCreatesFile(): void
    {
        $this->runTask(new FileTask(
            path: 'file',
            type: 'file',
        ));

        self::assertFileExists($this->workspace()->path('file'));
    }

    public function testCreatesFileWithContent(): void
    {
        $this->runTask(new FileTask(
            path: 'file',
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
            path: 'file',
            type: 'directory',
            content: 'Hello World',
        ));
    }

    public function testCreatesFileParentDirectories(): void
    {
        $this->runTask(new FileTask(
            path: 'barfoo/foobar/file',
            type: 'file',
        ));

        self::assertFileExists($this->workspace()->path('barfoo/foobar/file'));
    }

    public function testCreatesFileWithPermission(): void
    {
        $this->runTask(new FileTask(
            path: 'README.md',
            type: 'file',
            mode: 0777
        ));
        $info = new SplFileInfo($this->workspace()->path('README.md'));
        self::assertEquals('0777', substr(sprintf('%o', fileperms($this->workspace()->path('README.md'))), -4));
    }

    protected function createHandler(): Handler
    {
        return new FileHandler(
            new Filesystem($this->workspace()->path(), '/'),
            new NullLogger()
        );
    }
}
