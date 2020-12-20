<?php

namespace Maestro\Tests\Unit\Core\Task;

use Maestro\Core\Fact\CwdFact;
use Maestro\Core\Task\Context;
use Maestro\Core\Task\Exception\TaskError;
use Maestro\Core\Task\FileTask;
use SplFileInfo;

class FileHandlerTest extends HandlerTestCase
{
    public function testCreatesDirectory(): void
    {
        $this->runTask(new FileTask(
            path: 'foobar/directory',
            type: 'directory',
        ));
        self::assertFileExists($this->filesystem()->localPath('foobar/directory'));
    }

    public function testCreatesDirectoryInCwd(): void
    {
        $this->runTask(new FileTask(
            path: '../barfoo/directory',
            type: 'directory',
        ), Context::create([], [
            new CwdFact('foobar')
        ]));
        self::assertFileExists($this->filesystem()->localPath('barfoo/directory'));
    }

    public function testIgnoresExistingDirectory(): void
    {
        $this->filesystem()->createDirectory('foobar/directory');
        $this->runTask(new FileTask(
            path: 'foobar/directory',
            type: 'directory',
        ));
        self::assertFileExists($this->filesystem()->localPath('foobar/directory'));
    }

    public function testRemovesDirectoryIfExistingIsFalse(): void
    {
        $this->filesystem()->createDirectory('foobar/directory');
        self::assertFileExists($this->filesystem()->localPath('foobar/directory'));
        $this->runTask(new FileTask(
            path: 'foobar/directory',
            type: 'directory',
            exists: false
        ));
        self::assertFileDoesNotExist($this->filesystem()->localPath('foobar/directory'));
    }

    public function testExceptionWhenFirectoryIsAFile(): void
    {
        $this->expectException(TaskError::class);
        $this->expectExceptionMessage('to be a directory');
        $this->filesystem()->putContents('foobar/directory', 'file!');
        self::assertFileExists($this->filesystem()->localPath('foobar/directory'));
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

        self::assertFileExists($this->filesystem()->localPath('file'));
    }

    public function testCreatesFileWithContent(): void
    {
        $this->runTask(new FileTask(
            path: 'file',
            type: 'file',
            content: 'Hello World',
        ));

        self::assertFileExists($this->filesystem()->localPath('file'));
        self::assertEquals('Hello World', $this->filesystem()->getContents('file'));
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

        self::assertFileExists($this->filesystem()->localPath('barfoo/foobar/file'));
    }

    public function testCreatesFileWithPermission(): void
    {
        $this->runTask(new FileTask(
            path: 'README.md',
            type: 'file',
            mode: 0777
        ));
        $info = new SplFileInfo($this->filesystem()->localPath('README.md'));
        self::assertEquals('0777', substr(sprintf('%o', fileperms($this->filesystem()->localPath('README.md'))), -4));
    }
}
