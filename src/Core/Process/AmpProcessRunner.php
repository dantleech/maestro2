<?php

namespace Maestro2\Core\Process;

use Amp\ByteStream\LineReader;
use Amp\Deferred;
use Amp\Process\Process;
use Amp\Process\ProcessInputStream;
use Amp\Promise;
use Maestro2\Core\Process\Exception\ProcessFailure;
use Psr\Log\LoggerInterface;
use function Amp\asyncCall;
use function Amp\call;

class AmpProcessRunner implements ProcessRunner
{
    private int $running = 0;
    private array $locks = [];

    public function __construct(private LoggerInterface $logger, private int $concurrency = 4, private bool $verbose = true)
    {
    }

    public function mustRun(array $args, ?string $cwd = null): Promise
    {
        return call(function () use ($args, $cwd) {
            $result = yield $this->run($args, $cwd);

            if (0 !== $result->exitCode()) {
                throw ProcessFailure::fromResult($result, $args);
            }

            return $result;
        });
    }

    /**
     * @return Promise<ProcessResult>
     */
    public function run(array $args, ?string $cwd = null) : Promise
    {
        return call(function () use ($args, $cwd) {
            if ($this->running >= $this->concurrency) {
                $this->logger->debug(sprintf(
                    'Process concurrency limit "%s" reached, waiting',
                    $this->concurrency
                ));
                $lock = new Deferred();
                $this->locks[] = $lock;
                yield $lock->promise();
            }

            $process = new Process($args, $cwd);
            $this->running++;
            $pid = yield $process->start();

            $this->logger->debug(sprintf(
                'pid:%s cwd:%s %s',
                $pid,
                $cwd ?? '<none>',
                implode(' ', array_map('escapeshellarg', $args)),
            ));

            $stdOut = $stdErr = '';
            asyncCall(function (ProcessInputStream $stream, int $pid) use (&$stdErr) {
                $reader = new LineReader($stream);
                while (null !== $line = yield $reader->readLine()) {
                    $stdErr .= $line . "\n";
                    if ($this->verbose) {
                        $this->logger->info(sprintf('pid:%s ERR: %s', $pid, $line));
                    }
                }
            }, $process->getStderr(), $pid);

            asyncCall(function (ProcessInputStream $stream, int $pid) use (&$stdOut) {
                $reader = new LineReader($stream);
                while (null !== $line = yield $reader->readLine()) {
                    $stdOut .= $line . "\n";
                    if ($this->verbose) {
                        $this->logger->info(sprintf('pid:%s OUT: %s', $pid, $line));
                    }
                }
            }, $process->getStdout(), $pid);


            $exitCode = yield $process->join();

            $this->running--;

            if ($lock = array_shift($this->locks)) {
                $lock->resolve();
            }

            (function (string $message) use ($exitCode) {
                if ($exitCode === 0) {
                    $this->logger->info($message);
                    return;
                }
                $this->logger->error($message);
            })(sprintf(
                'pid:%s %s exited with %s',
                $pid,
                implode(' ', array_map('escapeshellarg', $args)),
                $exitCode,
            ));

            return new ProcessResult(
                $exitCode,
                $stdOut,
                $stdErr,
                $args,
                $cwd,
            );
        });
    }
}
