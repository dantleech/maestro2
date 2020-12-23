<?php

namespace Maestro\Composer;

use Maestro\Composer\Exception\ComposerJsonNotFound;
use Maestro\Core\Exception\RuntimeException;
use function json_decode;

final class ComposerJson
{
    public function __construct(private array $object)
    {
    }

    public static function fromProjectRoot(string $projectRoot): self
    {
        return new self((function (string $composerPath): array {
            if (!file_exists($composerPath)) {
                throw new ComposerJsonNotFound(sprintf(
                    'Composer JSON file does not exist at "%s"',
                    $composerPath
                ));
            }

            return (function (false|string $contents): array {
                if (false === $contents) {
                    throw new RuntimeException(
                        'Could not read file'
                    );
                }
                return (function (mixed $composer): array {
                    if (!$composer) {
                        return [];
                    }

                    if (!is_array($composer)) {
                        throw new RuntimeException(
                            'Invalid composer JSON file'
                        );
                    }

                    return $composer;
                })(json_decode($contents, true, 512, JSON_THROW_ON_ERROR));
            })(file_get_contents($composerPath));
        })($projectRoot . '/composer.json'));
    }

    public function autoloadPaths(): array
    {
        return array_reduce(['autoload', 'autoload-dev'], function (array $paths, string $autoload) {
            if (!isset($this->object[$autoload])) {
                return $paths;
            }
            return array_merge($paths, array_reduce(
                ['psr-4', 'psr-0'],
                function (array $paths, string $autoloadType) use ($autoload) {
                    if (!isset($this->object[$autoload][$autoloadType])) {
                        return $paths;
                    }

                    return array_values((array)$this->object[$autoload][$autoloadType]);
                },
                []
            ));
        }, []);

        return $paths;
    }

    public function branchAliases(): array
    {
        if (!isset($this->object->extra->{'branch-alias'})) {
            return [];
        }

        return (array)$this->object->extra->{'branch-alias'};
    }

    public function packages(): ComposerPackages
    {
        $required = array_merge(
            $this->object['require'] ?? [],
            $this->object['require-dev'] ?? [],
        );
        return new ComposerPackages(array_values(array_map(function (string $name, string $version) {
            return new ComposerPackage($name, $version);
        }, array_keys($required), array_values($required))));
    }

    public static function fromArray(array $array): self
    {
        return new self($array);
    }
}
