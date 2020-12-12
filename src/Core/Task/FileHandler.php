<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Task\Exception\TaskError;
use Psr\Log\LoggerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
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

    public function run(Task $task, Context $context): Promise
    {
        return call(function () use ($task, $context) {
            assert($task instanceof FileTask);
            match ($task->type()) {
            'directory' => $this->handleDirectory($task),
                'file' => $this->handleFile($task),
                default => throw new RuntimeException(sprintf(
                    'Invalid file type "%s"',
                    $task->type()
                ))
            };

            return $context;
        });
    }

    private function handleDirectory(FileTask $task): void
    {
        if ($task->content()) {
            throw new TaskError(
                'Content provided but file type is "directory"'
            );
        }
        if (file_exists($task->path())) {
            if ($task->exists() === false) {
                $this->removeDirectory($task->path());
                return;
            }

            if (!is_dir($task->path())) {
                throw new TaskError(sprintf(
                    'Expected "%s" to be a directory, but it\'s not',
                    $task->path()
                ));
            }
            return;
        }

        mkdir($task->path(), $task->mode(), true);
    }

    private function removeDirectory(string $path): void
    {
        if (0 !== strpos($path, getcwd())) {
            throw new RuntimeException(sprintf(
                'Will not delete directory "%s" outside of your current working directory "%s"',
                $path,
                getcwd()
            ));
        }


        $this->logger->info(sprintf('Removing directory "%s" recursively', $path));

        $fs = new Filesystem();
        $fs->remove($path);
    }

    private function handleFile(FileTask $task): void
    {
        if ($task->exists() === false) {
            if (file_exists($task->path())) {
                return;
            }
            $fs = new Filesystem();
            $fs->remove($task->path());
            return;
        }

        $createDir = new FileTask(
            path: dirname($task->path()),
            type: 'directory',
            exists: true
        );

        $this->handleDirectory($createDir);

        file_put_contents($task->path(), $task->content() ?? '');
        chmod($task->path(), $task->mode());
    }
}
