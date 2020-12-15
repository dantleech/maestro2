<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Filesystem\WorkspaceFs;
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
    private WorkspaceFs $workspaceFs;

    public function __construct(
        WorkspaceFs $workspaceFs,
        private LoggerInterface $logger
    )
    {
        $this->workspaceFs = $workspaceFs;
        $this->logger = $logger;
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
        if ($this->workspaceFs->fileExists($task->path())) {
            if ($task->exists() === false) {
                $this->removeDirectory($task->path());
                return;
            }

            //if (!$this->workspaceFs->mimeType($task->path())) {
            //    throw new TaskError(sprintf(
            //        'Expected "%s" to be a directory, but it\'s not',
            //        $task->path()
            //    ));
            //}
            return;
        }

        $this->workspaceFs->createDirectory($task->path(), [
            'visibility' => (string)$task->mode()
        ]);
    }

    private function removeDirectory(string $path): void
    {
        $this->logger->info(sprintf('Removing directory "%s" recursively', $path));
        $this->workspaceFs->deleteDirectory($path);
    }

    private function handleFile(FileTask $task): void
    {
        if ($task->exists() === false) {
            $this->workspaceFs->delete($task->path());
            return;
        }

        $createDir = new FileTask(
            path: dirname($task->path()),
            type: 'directory',
            exists: true
        );

        $this->handleDirectory($createDir);

        $this->workspaceFs->write($task->path(), $task->content() ?? '', [
            'visibility' => (string)$task->mode()
        ]);
    }
}
