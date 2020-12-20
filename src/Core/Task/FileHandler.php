<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Maestro\Core\Fact\CwdFact;
use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\Task\Exception\TaskError;
use Psr\Log\LoggerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use function Amp\call;

class FileHandler implements Handler
{
    private Filesystem $workspaceFs;

    public function __construct(
        Filesystem $workspaceFs,
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
        return call(function (Filesystem $filesystem) use ($task, $context) {
            assert($task instanceof FileTask);
            match ($task->type()) {
                'directory' => $this->handleDirectory($filesystem, $task),
                'file' => $this->handleFile($filesystem, $task),
                default => throw new RuntimeException(sprintf(
                    'Invalid file type "%s"',
                    $task->type()
                ))
            };

            return $context;
        }, $this->workspaceFs->cd($context->factOrNull(CwdFact::class)?->cwd() ?: '/'));
    }

    private function handleDirectory(Filesystem $filesystem, FileTask $task): void
    {
        if ($task->content()) {
            throw new TaskError(
                'Content provided but file type is "directory"'
            );
        }
        if ($filesystem->exists($task->path())) {
            if ($task->exists() === false) {
                $filesystem->remove($task->path());
                return;
            }

            if (!$filesystem->isDirectory($task->path())) {
                throw new TaskError(sprintf(
                    'Expected "%s" to be a directory, but it\'s not',
                    $task->path()
                ));
            }
            return;
        }

        $filesystem->createDirectory($task->path());
        $filesystem->setMode($task->path(), $task->mode());
    }

    private function handleFile(Filesystem $filesystem, FileTask $task): void
    {
        if ($task->exists() === false) {
            $filesystem->remove($task->path());
            return;
        }

        $createDir = new FileTask(
            path: dirname($task->path()),
            type: 'directory',
            exists: true
        );

        $this->handleDirectory($filesystem, $createDir);

        $filesystem->putContents($task->path(), $task->content());
        $filesystem->setMode($task->path(), $task->mode());
    }
}
