<?php

namespace Maestro\Development;

use Webmozart\PathUtil\Path;

class TaskDocBuilder
{
    public function buildDoc(TaskMetadata $taskMeta): string
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

        return implode("\n", $out);
    }
}
