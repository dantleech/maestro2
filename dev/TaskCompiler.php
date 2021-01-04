<?php

namespace Maestro\Development;

use Psr\Log\LoggerInterface;
use Webmozart\PathUtil\Path;

class TaskCompiler
{
    public function __construct(
        private LoggerInterface $logger,
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

        $this->logger->info('Generating task documentation');
        foreach ($this->finder->find() as $taskMeta) {
            $path = Path::join([$this->outPath, sprintf(
                    '%s.md', $taskMeta->name()
            )]);
            $this->logger->debug('[doc] '  . $path);
            file_put_contents(
                $path,
                $this->taskBuilder->buildDoc($taskMeta)
            );
        }
    }
}
