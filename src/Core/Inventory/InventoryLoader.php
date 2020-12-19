<?php

namespace Maestro2\Core\Inventory;

use DTL\Invoke\Invoke;
use Exception;
use Maestro2\Core\Inventory\Exception\CouldNotLoadConfig;
use function json_decode;

class InventoryLoader
{
    public function __construct(private string $defaultInventory)
    {
    }

    public function load(): MainNode
    {
        return $this->readConfig($this->defaultInventory);
    }

    private function readConfig(string $name): MainNode
    {
        if (is_readable($name)) {
            return $this->loadFile($name);
        }

        throw new CouldNotLoadConfig(sprintf(
            'Inventory "%s" not found',
            $name
        ));
    }

    private function loadFile(string $filename): MainNode
    {
        if (false === $contents = file_get_contents($filename)) {
            throw CouldNotLoadConfig::couldNotReadFile($filename);
        }

        try {
            $config = json_decode($contents, true, 512, \JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {
            throw CouldNotLoadConfig::couldNotDecodeJson($exception);
        }

        $mainNode = Invoke::new(MainNode::class, (array)$config);
        assert($mainNode instanceof MainNode);

        return $mainNode;
    }
}
