<?php

namespace Maestro\Composer;

use Countable;
use Maestro\Core\Exception\RuntimeException;

class ComposerPackages implements Countable
{
    /**
     * @var array<string,ComposerPackage>
     */
    private array $packages;

    /**
     * @param list<ComposerPackage> $packages
     */
    public function __construct(array $packages)
    {
        $this->packages = array_combine(array_map(
            fn (ComposerPackage $package) => $package->name(),
            $packages,
        ), array_values($packages));
    }

    public static function fromArray(array $packageArray): self
    {
        return new self(array_map(
            fn (string $name, string $version) => new ComposerPackage($name, $version),
            array_keys($packageArray),
            array_values($packageArray)
        ));
    }

    public function get(string $name): ComposerPackage
    {
        if (!isset($this->packages[$name])) {
            throw new RuntimeException(sprintf(
                'Could not find package "%s" known packages: "%s"',
                $name,
                implode('", "', array_keys($this->packages))
            ));
        }

        return $this->packages[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->packages[$name]);
    }

    public function count(): int
    {
        return count($this->packages);
    }
}
