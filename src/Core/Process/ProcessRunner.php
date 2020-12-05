<?php

namespace Maestro2\Core\Process;

use Amp\ByteStream\InputStream;
use Amp\ByteStream\LineReader;
use Amp\Deferred;
use Amp\Process\Process;
use Amp\Process\ProcessInputStream;
use Amp\Promise;
use Maestro2\Core\Process\Exception\ProcessFailure;
use Psr\Log\LoggerInterface;
use function Amp\ByteStream\buffer;
use function Amp\asyncCall;
use function Amp\call;
use function Amp\delay;

class ProcessRunner
{
    private int $running = 0;
    private array $locks = [];

    public function __construct(private LoggerInterface $logger, private int $concurrency = 10)
    {
    }

    public function mustRun(array $args): Promise
    {
        return call(function () use ($args) {
            $result = yield $this->run($args);

            if (0 !== $result->exitCode()) {
                throw new ProcessFailure(sprintf(
                    '`%s` exited with code "%s"',
                    implode(' ', $args),
                    $result->exitCode()
                ));
            }
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

            asyncCall(function (ProcessInputStream $stream, int $pid) {
                $reader = new LineReader($stream);
                while ($line = yield $reader->readLine()) {
                    $this->logger->info(sprintf('pid:%s %s', $pid, $line));
                }
            }, $process->getStderr(), $pid);

            $exitCode = yield $process->join();

            $this->running--;

            if ($lock = array_shift($this->locks)) {
                $lock->resolve();
            }

            return new ProcessResult(
                $exitCode
            );
        });
    }
}
