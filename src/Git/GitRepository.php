<?php

namespace Maestro2\Git;

use Amp\Promise;
use Amp\Success;
use Generator;
use Maestro2\Core\Process\ProcessResult;
use Maestro2\Core\Process\ProcessRunner;
use Maestro2\Core\Script\ScriptResult;
use Maestro2\Core\Script\ScriptRunner;
use Maestro2\Core\Vcs\Exception\CheckoutError;
use Maestro2\Core\Vcs\Repository;
use Maestro2\Core\Vcs\Tag;
use Maestro2\Core\Vcs\Tags;
use Maestro2\Git\Exception\GitException;
use Psr\Log\LoggerInterface;
use function Amp\call;

class GitRepository implements Repository
{
    public function __construct(private ProcessRunner $runner, private LoggerInterface $logger, private string $cwd)
    {
    }

    public function isCheckedOut(): bool
    {
        return file_exists($this->cwd . '/.git');
    }

    /**
     * {@inheritDoc}
     */
    public function checkout(string $url, string $target): Promise
    {
        return call(function () use ($url, $target) {
            $result = yield $this->runner->run(
                args: [
                    'git',
                    'clone',
                    '--depth=1',
                    $url,
                    $target
                ]
            );
            assert($result instanceof ProcessResult);

            if ($result->exitCode() !== 0) {
                throw new CheckoutError(sprintf(
                    'Could not clone "%s" to "%s" exit code "%s": %s',
                    $url,
                    $this->cwd,
                    $result->exitCode(),
                    $result->stderr()
                ));
            }

            return new Success();
        });
    }

    public function listTags(): Promise
    {
        return call(function () {
            $result = yield $this->runner->run([
                'git',
                'tag',
                '--format=%(refname:strip=2) %(objectname)"'
            ], $this->cwd);
            assert($result instanceof ProcessResult);

            if ($result->exitCode() !== 0) {
                throw new GitException(sprintf(
                    'Could not list tags in "%s"',
                    $this->cwd
                ));
            }

            return new Tags(array_map(function ($tag) {
                return new Tag($tag[0], $tag[1]);
            }, array_filter(
                array_map(
                    function (string $line) {
                        return array_filter(array_map(
                            'trim',
                            explode(' ', $line)
                        ));
                    },
                    explode(
                        "\n",
                        $result->stdout()
                    )
                )
            )));
        });
    }

    public function tag(string $tag): Promise
    {
        return \Amp\call(function () use ($tag) {
            $result = yield $this->runner->run([
                'git',
                'tag',
                $tag
            ], $this->cwd);
            assert($result instanceof ProcessResult);

            if ($result->exitCode() !== 0) {
                if (strpos($result->stderr(), 'already exists')) {
                    $this->logger->info('Ignoring already existing tag');
                    return null;
                }

                throw new GitException(sprintf(
                    'Could not list tags in "%s": %s',
                    $this->cwd,
                    $result->stderr()
                ));
            }

            $this->logger->info(sprintf('Tagged "%s"', $tag));

            return null;
        });
    }

    public function headId(): Promise
    {
        return \Amp\call(function () {
            $result = yield $this->runner->run([
                'git',
                'rev-parse',
                'HEAD'
            ], $this->cwd);
            assert($result instanceof ProcessResult);

            if ($result->exitCode() !== 0) {
                throw new GitException(sprintf(
                    'Could not parse current revision in "%s"',
                    $this->cwd
                ));
            }

            return trim($result->stdout());
        });
    }

    public function commitsBetween(string $start, string $end): Promise
    {
        return \Amp\call(function () use ($start, $end) {
            $result = yield $this->runner->run([
                'git',
                'rev-list',
                sprintf(' %s...%s',
                    $start,
                    $end
                ),
            ], $this->cwd);

            assert($result instanceof ProcessResult);

            if ($result->exitCode() !== 0) {
                throw new GitException(sprintf(
                    'Could not list commit Ids between "%s" and "%s" in "%s"',
                    $start,
                    $end,
                    $this->cwd
                ));
            }

            return array_values(array_filter(array_map('trim', explode("\n", $result->stdout()))));
        });
    }

    public function message(string $commitId): Promise
    {
        return \Amp\call(function () use ($commitId) {
            $result = yield $this->runner->run([
                'git',
                'log',
                $commitId,
                '-1',
                '--pretty=%%B',
            ], $this->cwd);
            assert($result instanceof ProcessResult);

            if ($result->exitCode() !== 0) {
                throw new GitException(sprintf(
                    'Could not read commit message for "%s" in "%s"',
                    $commitId,
                    $this->cwd
                ));
            }

            return trim($result->stdout());
        });
    }

    public function update(): Promise
    {
        return \Amp\call(function () {
            yield from $this->execGit(['reset',  '--hard']);
            yield from $this->execGit(['clean',  '-fd']);
            yield from $this->execGit(['pull']);

            return null;
        });
    }

    /**
     * @param list<string> $cmd
     */
    private function execGit(array $cmd): Generator
    {
        $result = yield $this->runner->run(array_values(array_merge(['git'], $cmd)), $this->cwd);

        if ($result->exitCode() !== 0) {
            throw new GitException(sprintf(
                'Could not execute "%s" in "%s": %s',
                implode(' ', $cmd),
                $this->cwd,
                $result->stderr()
            ));
        }

        return $result;
    }
}
