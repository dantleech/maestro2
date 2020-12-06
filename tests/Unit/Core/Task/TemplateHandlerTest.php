<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Task\TemplateTask;
use Maestro2\Core\Task\TemplateHandler;
use Maestro2\Tests\IntegrationTestCase;
use SplFileInfo;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use function Amp\Promise\wait;

class TemplateHandlerTest extends IntegrationTestCase
{
    public function testAppliesTemplate(): void
    {
        $this->workspace()->reset();
        $this->workspace()->put('README.md.twig', 'Hello world');

        $handler = TemplateHandler::createForBasePath($this->workspace()->path());
        wait($handler->run(new TemplateTask(
            template: 'README.md.twig',
            target: 'README.md',
        )));
        self::assertFileExists($this->workspace()->path('README.md'));
        self::assertEquals('Hello world', file_get_contents($this->workspace()->path('README.md')));
    }

    public function testAppliesTemplateWithVars(): void
    {
        $this->workspace()->reset();
        $this->workspace()->put('README1.md.twig', 'Hello {{ name }}');

        $handler = TemplateHandler::createForBasePath($this->workspace()->path());
        wait($handler->run(new TemplateTask(
            template: 'README1.md.twig',
            target: 'README1.md',
            vars: [
                'name' => 'Bob',
            ]
        )));
        self::assertFileExists($this->workspace()->path('README1.md'));
        self::assertEquals('Hello Bob', file_get_contents($this->workspace()->path('README1.md')));
    }

    public function testTemplateWithMode(): void
    {
        $this->workspace()->reset();
        $this->workspace()->put('README.md.twig', 'Hello {{ name }}');

        $handler = TemplateHandler::createForBasePath($this->workspace()->path());
        wait($handler->run(new TemplateTask(
            template: 'README.md.twig',
            mode: 0777,
            target: 'README.md',
            vars: [
                'name' => 'Bob',
            ]
        )));
        self::assertFileExists($this->workspace()->path('README.md'));
        $info = new SplFileInfo($this->workspace()->path('README.md'));
        self::assertEquals('0777', substr(sprintf('%o', fileperms($this->workspace()->path('README.md'))), -4));
    }

    public function testDoesNotOverwriteByDefault(): void
    {
        $this->workspace()->reset();
        $this->workspace()->put('README2.md.twig', 'Boo');
        $this->workspace()->put('README.md', 'Baz');

        $handler = TemplateHandler::createForBasePath($this->workspace()->path());
        wait($handler->run(new TemplateTask(
            template: 'README2.md.twig',
            target: 'README.md'
        )));
        self::assertFileExists($this->workspace()->path('README.md'));
        self::assertEquals('Baz', file_get_contents($this->workspace()->path('README.md')));
    }

    public function testOverwrites(): void
    {
        $this->workspace()->reset();
        $this->workspace()->put('README2.md.twig', 'Boo');
        $this->workspace()->put('README.md', 'Baz');

        $handler = TemplateHandler::createForBasePath($this->workspace()->path());
        wait($handler->run(new TemplateTask(
            template: 'README2.md.twig',
            target: 'README.md',
            overwrite: true
        )));
        self::assertFileExists($this->workspace()->path('README.md'));
        self::assertEquals('Boo', file_get_contents($this->workspace()->path('README.md')));
    }

    public function testCreatesNonExistingDirectories(): void
    {
        $this->workspace()->reset();
        $this->workspace()->put('README2.md.twig', 'Boo');
        $this->workspace()->put('README.md', 'Baz');

        $handler = TemplateHandler::createForBasePath($this->workspace()->path());
        wait($handler->run(new TemplateTask(
            template: 'README2.md.twig',
            target: 'foobar/barfoo/README.md',
            overwrite: true
        )));
        self::assertFileExists($this->workspace()->path('foobar/barfoo/README.md'));
    }
}
