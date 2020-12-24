<?php

namespace Maestro\Core\Extension\Config;

use RuntimeException;

class RuntimeConfig
{
    private ?string $branch = null;

    private bool $loaded = false;

    public function load(array $configuration): void
    {
        if ($this->loaded) {
            throw new RuntimeException(
                'Cannot runtime configuration already loaded, cannot load again'
            );
        }

        foreach ($configuration as $key => $value) {
            if (!isset($this->$key)) {
                throw new RuntimeException(sprintf(
                    'Unknown runtime key "%s"', $key
                ));
            }

            $this->$key = $value;
        }

        $this->loaded = true;
    }

    public function branch(): ?string
    {
        return $this->branch;
    }
}
