<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Task\TemplateTask;
use SplFileInfo;

class TemplateHandlerTest extends HandlerTestCase
{
    public function testAppliesTemplate(): void
    {
        $this->workspace()->put('templates/README.md.twig', 'Hello world');

        $this->runTask(new TemplateTask(
            template: 'README.md.twig',
            target: 'README.md',
        ));
        self::assertFileExists($this->filesystem()->localPath('README.md'));
        self::assertEquals('Hello world', file_get_contents($this->filesystem()->localPath('README.md')));
    }

    public function testAppliesTemplateWithVars(): void
    {
        $this->workspace()->put('templates/README1.md.twig', 'Hello {{ name }}');

        $this->runTask(new TemplateTask(
            template: 'README1.md.twig',
            target: 'README1.md',
            vars: [
                'name' => 'Bob',
            ]
        ));
        self::assertFileExists($this->filesystem()->localPath('README1.md'));
        self::assertEquals('Hello Bob', file_get_contents($this->filesystem()->localPath('README1.md')));
    }

    public function testTemplateWithMode(): void
    {
        $this->workspace()->put('templates/README.md.twig', 'Hello {{ name }}');

        $this->runTask(new TemplateTask(
            template: 'README.md.twig',
            mode: 0777,
            target: 'README.md',
            vars: [
                'name' => 'Bob',
            ]
        ));
        self::assertFileExists($this->filesystem()->localPath('README.md'));
        $info = new SplFileInfo($this->filesystem()->localPath('README.md'));
        self::assertEquals('0777', substr(sprintf('%o', fileperms($this->filesystem()->localPath('README.md'))), -4));
    }

    public function testDoesNotOverwriteByDefault(): void
    {
        $this->workspace()->put('templates/README2.md.twig', 'Boo');
        $this->workspace()->put('workspace/README.md', 'Baz');

        $this->runTask(new TemplateTask(
            template: 'README2.md.twig',
            target: 'README.md'
        ));
        self::assertFileExists($this->filesystem()->localPath('README.md'));
        self::assertEquals('Baz', file_get_contents($this->filesystem()->localPath('README.md')));
    }

    public function testOverwrites(): void
    {
        $this->workspace()->put('templates/README2.md.twig', 'Boo');
        $this->workspace()->put('workspace/README.md', 'Baz');

        $this->runTask(new TemplateTask(
            template: 'README2.md.twig',
            target: 'README.md',
            overwrite: true
        ));
        self::assertFileExists($this->filesystem()->localPath('README.md'));
        self::assertEquals('Boo', file_get_contents($this->filesystem()->localPath('README.md')));
    }

    public function testCreatesNonExistingDirectories(): void
    {
        $this->workspace()->put('templates/README2.md.twig', 'Boo');
        $this->workspace()->put('workspace/README.md', 'Baz');

        $this->runTask(new TemplateTask(
            template: 'README2.md.twig',
            target: 'foobar/barfoo/README.md',
            overwrite: true
        ));
        self::assertFileExists($this->filesystem()->localPath('foobar/barfoo/README.md'));
    }
}
