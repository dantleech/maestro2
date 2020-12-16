<?php

namespace Maestro2\Core\Filesystem;

use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Webmozart\PathUtil\Path;

class Filesystem
{
    private SymfonyFilesystem $fs;

    public function __construct(private string $rootDir, private string $cwd)
    {
        $this->fs = new SymfonyFilesystem();
    }

    public function exists(string $path): bool
    {
        return file_exists($this->resolvePath($path));
    }

    public function putContents(string $path, ?string $contents = null): void
    {
        file_put_contents($this->resolvePath($path), $contents ?? '');
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

    public function createDirectory(string $path): void
    {
        $this->fs->mkdir($this->resolvePath($path));
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
}
