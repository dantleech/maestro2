<?php

namespace Maestro2\Core\Config;

use DTL\Invoke\Invoke;
use Exception;
use Maestro2\Core\Config\Exception\CouldNotLoadConfig;
use RuntimeException;
use function json_decode;

class ConfigLoader
{
    private array $filenames;

    public function __construct(array $filenames)
    {
        $this->filenames = $filenames;
    }

    public function load(): MainNode
    {
        return $this->readConfig();
    }

    private function loadFile($filename): MainNode
    {
        if (false === $contents = file_get_contents($filename)) {
            throw CouldNotLoadConfig::couldNotReadFile($filename);
        }

        try {
            $config = json_decode($contents, true, 512, \JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {
            throw CouldNotLoadConfig::couldNotDecodeJson($exception);
        }

        $mainNode = Invoke::new(MainNode::class, $config);
        assert($mainNode instanceof MainNode);

        return $mainNode;
    }

    private function readConfig(): MainNode
    {
        foreach ($this->filenames as $filename) {
            if (is_readable($filename)) {
                return $this->loadFile($filename);
            }
        }

        throw CouldNotLoadConfig::noConfigFileFound($this->filenames);
    }
}
