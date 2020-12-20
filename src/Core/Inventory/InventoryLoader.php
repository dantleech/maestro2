<?php

namespace Maestro\Core\Inventory;

use DTL\Invoke\Invoke;
use Exception;
use Maestro\Core\Inventory\Exception\CouldNotLoadConfig;
use function json_decode;

class InventoryLoader
{
    /**
     * @param list<string> $inventories
     */
    public function __construct(private array $inventories)
    {
    }

    public function load(): MainNode
    {
        return $this->readConfig($this->inventories);
    }

    private function readConfig(array $paths): MainNode
    {
        $config = array_reduce(
            $paths,
            fn (array $initial, string $path) => array_replace_recursive($initial, $this->readFile($path)),
            []
        );
        $mainNode = Invoke::new(MainNode::class, $config);
        assert($mainNode instanceof MainNode);

        return $mainNode;
    }

    private function readFile(string $path): array
    {
        if (!is_readable($path)) {
            throw new CouldNotLoadConfig(sprintf(
                'Inventory "%s" not found',
                $path
            ));
        }

        if (false === $contents = file_get_contents($path)) {
            throw CouldNotLoadConfig::couldNotReadFile($path);
        }

        try {
            $config = json_decode($contents, true, 512, \JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {
            throw new CouldNotLoadConfig(sprintf(
                'Could not decode JSON file "%s": %s',
                $path,
                $exception->getMessage()
            ), 0, $exception);
        }

        return (array)$config;
    }
}
