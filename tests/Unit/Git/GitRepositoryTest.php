<?php

namespace Maestro2\Tests\Unit\Git;

use Maestro2\Core\Process\AmpProcessRunner;
use Maestro2\Core\Vcs\Tags;
use Maestro2\Git\GitRepository;
use Maestro2\Tests\IntegrationTestCase;
use Psr\Log\NullLogger;
use RuntimeException;
use Symfony\Component\Process\Process;
use function Amp\Promise\wait;

class GitRepositoryTest extends IntegrationTestCase
{
    const EXAMPLE_REPO_PATH = 'base';
    const EXAMPLE_REMOTE_REPO_PATH = 'source';


    /**
     * @var ScriptRunner
     */
    private $runner;

    /**
     * @var GitRepository
     */
    private $gitRepository;

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->initExampleRepository(self::EXAMPLE_REPO_PATH);

        $logger = new NullLogger();

        $this->runner = new AmpProcessRunner($logger);
        $this->gitRepository = new GitRepository(
            $this->runner,
            $logger,
            $this->workspace()->path(self::EXAMPLE_REPO_PATH)
        );
    }

    public function testCheckout(): void
    {
        $this->workspace()->reset();
        $this->initExampleRepository(self::EXAMPLE_REMOTE_REPO_PATH);

        $this->assertFileDoesNotExist($this->workspace()->path(self::EXAMPLE_REPO_PATH));

        wait($this->gitRepository->checkout(
            $this->workspace()->path(self::EXAMPLE_REMOTE_REPO_PATH),
        ));

        $this->assertFileExists($this->workspace()->path(self::EXAMPLE_REPO_PATH));
        $this->assertFileExists($this->workspace()->path(self::EXAMPLE_REPO_PATH . '/README.md'));
    }

    /**
     * @dataProvider provideListTags
     */
    public function testListTags(array $tags, ?array $expectedTags = null)
    {
        if (null === $expectedTags) {
            $expectedTags = $tags;
        }

        foreach ($tags as $tag) {
            $this->exec('git tag '.$tag);
        }

        $this->assertEquals(
            $expectedTags,
            wait($this->gitRepository->listTags())->names()
        );
    }

    public function provideListTags()
    {
        yield 'empty' => [
            []
        ];

        yield 'single tag' => [
            [ '1.0.0' ],
        ];

        yield 'multiple tags 1' => [
            [ '1.0.0', '1.0.1' ],
        ];

        yield 'sorts tags 1' => [
            [ '1', '3', '2' ],
            [ '1', '2', '3' ],
        ];

        yield 'sorts tags 2' => [
            [ '0.1.2', '0.1.0', '1.1.1', '100' ],
            [ '0.1.0', '0.1.2', '1.1.1' , '100'],
        ];
    }

    public function testTagsNewTag()
    {
        wait($this->gitRepository->tag('1.0.0'));
        $this->assertEquals([
            '1.0.0'
        ], wait($this->gitRepository->listTags())->names());
    }

    public function testIgnoresExistingTag()
    {
        $this->exec('git tag 1.0.0');

        wait(
            $this->gitRepository->tag('1.0.0')
        );

        $this->assertEquals(['1.0.0'], wait($this->gitRepository->listTags())->names());
    }

    public function testGetsHeadId()
    {
        $headId = wait($this->gitRepository->headId());
        $this->assertNotNull($headId);
    }

    public function testExistingTagsIncludeCommitId()
    {
        $this->exec('git tag 1.0.0');
        $tags = wait($this->gitRepository->listTags());
        $this->assertCount(1, $tags);
        assert($tags instanceof Tags);
        $tag = $tags->mostRecent();
        $this->assertEquals(
            wait($this->gitRepository->headId()),
            $tag->commitId()
        );
    }

    public function testCommitsBetween()
    {
        $this->exec('git tag 1.0.0');
        $this->workspace()->put('base/foobar1', '');
        $this->exec('git add foobar1');
        $this->exec('git commit -m "foobar1"');
        $this->workspace()->put('base/foobar2', '');
        $this->exec('git add foobar2');
        $this->exec('git commit -m "foobar2"');

        $commitIds = wait($this->gitRepository->commitsBetween(
            '1.0.0',
            wait($this->gitRepository->headId($this->workspace()->path('/')))
        ));
        $this->assertCount(2, $commitIds);
    }

    public function testComment()
    {
        $this->exec('git tag 1.0.0');
        $this->workspace()->put('base/foobar1', '');
        $this->exec('git add foobar1');
        $this->exec('git commit -m "Hello World"');

        $message = wait($this->gitRepository->message(
            wait($this->gitRepository->headId())
        ));
        $this->assertEquals('Hello World', $message);
    }

    public function testUpdate()
    {
        $this->workspace()->reset();
        $this->initExampleRepository(self::EXAMPLE_REMOTE_REPO_PATH);

        wait($this->gitRepository->checkout($this->workspace()->path(self::EXAMPLE_REMOTE_REPO_PATH), []));

        $this->workspace()->put(self::EXAMPLE_REMOTE_REPO_PATH . '/foobar1', 'hello');
        $this->exec('git add foobar1', self::EXAMPLE_REMOTE_REPO_PATH);
        $this->exec('git commit -m "Hello World"', self::EXAMPLE_REMOTE_REPO_PATH);

        $this->assertFileDoesNotExist($this->workspace()->path(self::EXAMPLE_REPO_PATH . '/foobar1', 'hello'));

        wait($this->gitRepository->update());

        $this->assertFileExists($this->workspace()->path(self::EXAMPLE_REPO_PATH . '/foobar1', 'hello'));
    }

    public function testUpdateWillResetAndClean()
    {
        $this->workspace()->reset();
        $this->initExampleRepository(self::EXAMPLE_REMOTE_REPO_PATH);

        wait($this->gitRepository->checkout($this->workspace()->path(self::EXAMPLE_REMOTE_REPO_PATH), []));

        $this->workspace()->put(self::EXAMPLE_REMOTE_REPO_PATH . '/foobar1', 'hello');
        $this->exec('git add foobar1', self::EXAMPLE_REMOTE_REPO_PATH);
        $this->exec('git commit -m "Hello World"', self::EXAMPLE_REMOTE_REPO_PATH);

        $this->assertFileDoesNotExist($this->workspace()->path(self::EXAMPLE_REPO_PATH . '/foobar1', 'hello'));
        $this->workspace()->put(self::EXAMPLE_REPO_PATH . '/foobar1', 'barfoo');

        wait($this->gitRepository->update());
    }

    private function exec(string $command, string $cwd = self::EXAMPLE_REPO_PATH): Process
    {
        $process = Process::fromShellCommandline($command, $this->workspace()->path($cwd));
        $process->run();

        if ($process->getExitCode() !== 0) {
            throw new RuntimeException(
                sprintf(
                    'Could not exec process "%s": %s%s',
                    $process->getCommandLine(),
                    $process->getOutput(),
                    $process->getErrorOutput()
                )
            );
        }

        return $process;
    }

    private function initExampleRepository($baseDir = self::EXAMPLE_REPO_PATH)
    {
        $this->workspace()->put($baseDir .'/README.md', 'Hello');
        
        $this->exec('git init', $baseDir);
        $this->exec('git add README.md', $baseDir);
        $this->exec('git commit -m "Initial"', $baseDir);
    }
}
