<?php

namespace Maestro\Core\Filesystem;

use Maestro\Core\Exception\RuntimeException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Webmozart\PathUtil\Path;

class Filesystem
{
    private SymfonyFilesystem $fs;

    public function __construct(private string $rootDir, private string $cwd = '/')
    {
        $this->fs = new SymfonyFilesystem();
    }

    /**
     * Resolve the given path to the absolute filesystem path relative
     * to the current current working directory.
     */
    public function localPath(?string $cwd = null): string
    {
        $cwd = $cwd ?? '.';
        return $this->resolvePath($cwd);
    }

    public function exists(string $path): bool
    {
        return file_exists($this->resolvePath($path));
    }

    public function putContents(string $path, ?string $contents = null): void
    {
        (function (string $path) use ($contents): void {
            $this->ensureDirectoryExists(dirname($path));
            file_put_contents($path, $contents ?? '');
        })($this->resolvePath($path));
    }

    public function setMode(string $path, int $mode): void
    {
        chmod($this->resolvePath($path), $mode);
    }

    public function remove(string $path): void
    {
        $this->fs->remove($this->resolvePath($path));
    }

    public function isDirectory(string $path): bool
    {
        return is_dir($this->resolvePath($path));
    }

    public function createDirectory(string $path, int $mode = 0744): void
    {
        $this->fs->mkdir($this->resolvePath($path));
        $this->setMode($path, $mode);
    }

    private function resolvePath(string $path): string
    {
        return Path::join([
            $this->rootDir,
            $this->cwd,
            $path
        ]);
    }

    public function cd(string $path): Filesystem
    {
        return new self(
            $this->rootDir,
            Path::makeAbsolute($path, $this->cwd)
        );
    }

    public function getContents(string $path): string
    {
        if (!file_exists($this->resolvePath($path))) {
            throw new RuntimeException(sprintf(
                'File "%s" does not exist',
                $path
            ));
        }

        return file_get_contents($this->resolvePath($path));
    }

    private function ensureDirectoryExists(string $path): void
    {
        if ($this->exists($path)) {
            return;
        }

        $this->fs->mkdir($path);
    }
}
