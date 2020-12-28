<?php

namespace Maestro\Composer\Task;

use Maestro\Core\Task\Task;
use Stringable;

/**
 * Manipulate and use Composer
 *
 * This task manipulates `composer.json`, uses the `composer` and provides
 * package information to downstream tasks.
 *
 * - Require and remove packages,
 * - Perform a composer update.
 * - Create `composer.json` if it doesn't exist.
 * - Creates the `Maestro\Composer\Fact\ComposerJsonFact`
 *
 * ## Require / remove packages
 *
 * The `require` package is the same as it's composer.json equivilent:
 *
 * ```php
 * new ComposerTask(
 *     require: [
 *         "my-package" => "^1.0"
 *     ]
 * )
 * ```
 *
 * *NOTE*: If the package exists in _exactly_ the same version the operation
 * will be skipped (i.e. it is idempotent).
 *
 * Removal:
 *
 * ```php
 * new ComposerTask(
 *     remove: [
 *         "my-package"
 *     ]
 * )
 * ```
 *
 * If you only wish to update existing packages (useful if you want to ensure
 * that a certain version of a package is shared by all your packgaes) you can
 * use the `intersection` option:
 *
 * ```php
 * new ComposerTask(
 *     intersection: true,
 *     requireDev: [
 *         "phpstan/phpstan" => "^0.12",
 *         "phpunit/phpunit" => "^9.0",
 *         "infection/phpunit" => "^18.0"
 *     ]
 * )
 * ```
 *
 * Above we update the (dev) version of these packages _only_ if they are found in `composer.json`.
 *
 * ## Update package
 *
 * By default the task will _not_ update composer.
 *
 * To update all packages:
 *
 * ```php
 * new ComposerTask(
 *     update: true
 * )
 * ```
 *
 * To update only specific packages:
 *
 * ```php
 * new ComposerTask(
 *     require: [
 *         'package/one' => '^1.0',
 *     ],
 *     update: true
 * )
 * ```
 *
 *
 */
class ComposerTask implements Task, Stringable
{
    /**
     * @param array<string, string> $require Use composer to require packages (`package` => `version`)
     * @param array<string, string> $requireDev Use composer to require dev packages (`package` => `version`)
     * @param list<string> $remove Use composer to remove packages
     * @param bool $dev Add requirements to `require-dev`
     * @param bool $intersection Only update packages if are already included in the existing `composer.json` (i.e. do not add packages)
     * @param string $composerBin Name of composer executable (will be detected automatically if omitted)
     * @param bool $update If composer update/install should be executed
     */
    public function __construct(
        private array $require = [],
        private array $requireDev = [],
        private array $remove = [],
        private bool $update = false,
        private bool $dev = false,
        private bool $intersection = false,
        private ?string $composerBin = null,
    ) {
    }

    public function remove(): array
    {
        return $this->remove;
    }

    /**
     * @return array<string,string>
     */
    public function require(): array
    {
        return $this->require;
    }

    /**
     * @return array<string,string>
     */
    public function requireDev(): array
    {
        return $this->requireDev;
    }

    public function update(): bool
    {
        return $this->update;
    }

    public function composerBin(): ?string
    {
        return $this->composerBin;
    }

    public function __toString(): string
    {
        return sprintf(
            'Updating composer: require [%s], require-dev [%s], remove: [%s], update %s',
            $this->dev ? 'yes' : 'no',
            implode(', ', array_map(
                fn (string $name, string $version) => sprintf('%s:%s', $name, $version),
                array_keys($this->require),
                array_values($this->require)
            )),
            implode(', ', array_map(
                fn (string $name, string $version) => sprintf('%s:%s', $name, $version),
                array_keys($this->requireDev),
                array_values($this->requireDev)
            )),
            implode(', ', array_keys($this->remove)),
            $this->update ? 'yes' : 'no'
        );
    }

    public function intersection(): bool
    {
        return $this->intersection;
    }
}
