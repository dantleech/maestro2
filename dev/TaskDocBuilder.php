<?php

namespace Maestro\Development;

use Webmozart\PathUtil\Path;

class TaskDocBuilder
{
    public function __construct(private TaskFinder $finder, private string $outPath)
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
            )]), implode("\n", $this->buildDoc($taskMeta)));
        }
    }

    private function buildDoc(TaskMetadata $taskMeta): array
    {
        $out = [];
        $out[] = '# ' . $taskMeta->name();
        $out[] = '';
        $out[] = sprintf('`%s`', $taskMeta->namespacedName());
        $out[] = '';
        $out[] = $taskMeta->shortDescription();
        $out[] = '';

        if ($taskMeta->parameters()) {
        $out[] = '## Parameters';
            foreach ($taskMeta->parameters() as $parameter) {
                assert($parameter instanceof TaskParameter);
                $out[] = sprintf('- **%s** %s - `%s`', ltrim($parameter->name(), '$'), $parameter->description(), $parameter->type());
            }
            $out[] = '';
        }
        $out[] = '## Description';
        $out[] = $taskMeta->documentation();

        return $out;
    }
}
