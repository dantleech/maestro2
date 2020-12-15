<?php

namespace Maestro2\Core\Filesystem\Flysystem;

use League\Flysystem\Local\LocalFilesystemAdapter as LeagueLocalFilesystemAdapter;
use League\Flysystem\PathPrefixer;
use League\Flysystem\UnixVisibility\VisibilityConverter;
use League\MimeTypeDetection\MimeTypeDetector;

class LocalFilesystemAdapter extends LeagueLocalFilesystemAdapter
{
    private PathPrefixer $prefixer;

    public function __construct(
        string $location,
        VisibilityConverter $visibility = null,
        int $writeFlags = LOCK_EX,
        int $linkHandling = self::DISALLOW_LINKS,
        MimeTypeDetector $mimeTypeDetector = null
    ) {
        parent::__construct($location, $visibility, $writeFlags, $linkHandling, $mimeTypeDetector);
        $this->prefixer = new PathPrefixer($location, DIRECTORY_SEPARATOR);
    }

    public function fileExists(string $location): bool
    {
        $location = $this->prefixer->prefixPath($location);

        return file_exists($location);
    }
}
