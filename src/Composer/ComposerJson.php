<?php

namespace Maestro2\Composer;

use Maestro2\Composer\Exception\ComposerJsonNotFound;
use Maestro2\Core\Exception\RuntimeException;
use stdClass;
use function json_decode;

final class ComposerJson
{
    public function __construct(private stdClass $object)
    {
    }

    public static function fromProjectRoot(string $projectRoot): self
    {
        return new self((function (string $composerPath): stdClass {
            if (!file_exists($composerPath)) {
                throw new ComposerJsonNotFound(sprintf(
                    'Composer JSON file does not exist at "%s"',
                    $composerPath
                ));
            }

            return (function (false|string $contents): stdClass {
                if (false === $contents) {
                    throw new RuntimeException(
                        'Could not read file'
                    );
                }
                return (function (mixed $composer): stdClass {
                    if (!$composer) {
                        return new stdClass();
                    }

                    if (!$composer instanceof stdClass && !is_array($composer)) {
                        throw new RuntimeException(
                            'Invalid composer JSON file'
                        );
                    }

                    return (object)$composer;
                })(json_decode($contents, false, 512, JSON_THROW_ON_ERROR));
            })(file_get_contents($composerPath));
        })($projectRoot . '/composer.json'));
    }

    public function autoloadPaths(): array
    {
        return array_reduce(['autoload', 'autoload-dev'], function (array $paths, string $autoload) {
            if (!isset($this->object->$autoload)) {
                return $paths;
            }
            return array_merge($paths, array_reduce(
                ['psr-4', 'psr-0'],
                function (array $paths, string $autoloadType) use ($autoload) {
                    if (!isset($this->object->{$autoload}->{$autoloadType})) {
                        return $paths;
                    }

                    return array_values((array)$this->object->$autoload->{$autoloadType});
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

    public static function fromArray(array $array): self
    {
        return new self((object)json_decode(json_encode($array), false));
    }
}
