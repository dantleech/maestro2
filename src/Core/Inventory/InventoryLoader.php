<?php

namespace Maestro\Core\Inventory;

use DTL\Invoke\Invoke;
use Exception;
use Maestro\Core\Inventory\Exception\CouldNotLoadConfig;
use Webmozart\Assert\Assert;
use function json_decode;

class InventoryLoader
{
    /**
     * @param list<string> $inventories
     */
    public function __construct(private array $inventories)
    {
    }

    public function load(array $variables = []): MainNode
    {
        return $this->readConfig($this->inventories, $variables);
    }

    private function readConfig(array $paths, array $variables): MainNode
    {
        $config = array_merge([
            'repositories' => [],
        ], array_reduce(
            $paths,
            fn (array $initial, string $path) => array_replace_recursive($initial, $this->readFile($path)),
            []
        ));

        $config = $this->mergeVariables($config, $variables);

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

    private function mergeVariables(array $config, array $variables): array
    {
        $config = array_merge([
            'vars' => [],
            'repositories' => [],
        ], $config);

        Assert::isArray($config['vars'], '"vars" must be an associative array, got "%s"');

        $config['vars'] = array_merge($config['vars'], $variables);

        foreach ($config['repositories'] as &$repository) {
            Assert::isArray($repository, '"repository" must be an associative array, got "%s"');
            $repository = array_merge([
                'vars' => [],
            ], $repository);
            Assert::isArray($repository['vars'], '"vars" must be an associative array, got "%s"');
            $repository['vars'] = array_merge($repository['vars'], $variables);
        }

        return $config;
    }
}
