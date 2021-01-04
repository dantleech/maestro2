<?php

namespace Maestro\Development;

use Webmozart\PathUtil\Path;

class TaskCompiler
{
    public function __construct(
        private TaskFinder $finder,
        private TaskDocBuilder $taskBuilder,
        private string $outPath
    )
    {
    }

    public function build(): void
    {
        if (!file_exists($this->outPath)) {
            @mkdir($this->outPath, 0777, true);
        }

        foreach ($this->finder->find() as $taskMeta) {
            file_put_contents(Path::join([$this->outPath, sprintf(
                '%s.md', $taskMeta->name()
            )]), $this->taskBuilder->buildDoc($taskMeta));
        }
    }
}
