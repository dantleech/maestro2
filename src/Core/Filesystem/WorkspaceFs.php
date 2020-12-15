<?php

namespace Maestro2\Core\Filesystem;

use League\Flysystem\Filesystem;
use Maestro2\Core\Config\MainNode;
use Maestro2\Core\Filesystem\Flysystem\LiteralVisibilityConverter;
use Maestro2\Core\Filesystem\Flysystem\LocalFilesystemAdapter;

class WorkspaceFs extends Filesystem
{
    public static function create(string $workspacePath): WorkspaceFs
    {
        return new self(new LocalFilesystemAdapter(
            $workspacePath,
            new LiteralVisibilityConverter()
        ));
    }
}
