<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Task\Exception\TaskError;
use Psr\Log\LoggerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use function Amp\call;

class FileHandler implements Handler
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function taskFqn(): string
    {
        return FileTask::class;
    }

    public function run(Task $task): Promise
    {
        return call(function () use ($task) {
            assert($task instanceof FileTask);
            return match ($task->type()) {
                'directory' => $this->handleDirectory($task),
                'file' => $this->handleFile($task),
            default => throw new RuntimeException(sprintf(
                'Invalid file type "%s"',
                $task->type()
            ))
            };
        });
    }

    private function handleDirectory(FileTask $task): void
    {
        if (file_exists($task->path())) {
            if ($task->exists() === false) {
                $this->removeDirectory($task->path());
                return;
            }

            if (!is_dir($task->path())) {
                throw new TaskError(sprintf(
                    'Expected "%s" to be a file, but it\'s not',
                    $task->path()
                ));
            }
            return;
        }

        mkdir($task->path(), $task->mode(), true);
    }

    private function removeDirectory(string $path)
    {
        if (0 !== strpos($path, getcwd())) {
            throw new RuntimeException(sprintf(
                'Will not delete directory "%s" outside of your current working directory "%s"',
                $path,
                getcwd()
            ));
        }

        $this->logger->info(sprintf('Removing directory "%s" recursively', $path));

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }

        rmdir($path);
    }
}
