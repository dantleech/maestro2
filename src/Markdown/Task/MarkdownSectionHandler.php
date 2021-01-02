<?php

namespace Maestro\Markdown\Task;

use Amp\Promise;
use League\CommonMark\Block\Element\Heading;
use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use League\CommonMark\Node\Node;
use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\Task\Context;
use Maestro\Core\Task\Handler;
use Maestro\Core\Task\S;
use Maestro\Core\Task\Task;
use function Amp\call;
use Maestro\Markdown\Task\MarkdownSectionTask;

class MarkdownSectionHandler implements Handler
{
    public function taskFqn(): string
    {
        return MarkdownSectionTask::class;
    }

    /**
     * {@inheritDoc}
     */
    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof MarkdownSectionTask);
        return call(function () use ($task, $context) {
            $environment = Environment::createCommonMarkEnvironment();
            $parser = new DocParser($environment);
            $filesystem = $context->service(Filesystem::class);
            $existing = $this->readContents($filesystem, $task);

            $document = $parser->parse($existing);
            $replaceHeader = $parser->parse($task->header())->firstChild();
            assert($replaceHeader instanceof Heading);

            $replaceStart = null;
            $replaceEnd = null;
            foreach ($document->children() as $childNode) {
                if ($childNode::class !== $replaceHeader::class) {
                    continue;
                }

                assert($childNode instanceof Heading);

                if ($replaceStart) {
                    $replaceEnd = $childNode->getStartLine() - 1;
                    break;
                }

                if ($childNode->getLevel() !== $replaceHeader->getLevel()) {
                    continue;
                }

                if ($childNode->getStringContent() !== $replaceHeader->getStringContent()) {
                    continue;
                }

                $replaceStart = $childNode->getStartLine();
            }

            $context->service(
                Filesystem::class
            )->putContents($task->path(), $this->repaceContent(
                $existing,
                $task->header(),
                $task->content(),
                $replaceStart,
                $replaceEnd
            ));

            return $context;
        });
    }

    private function repaceContent(
        string $existing,
        string $header,
        string $content,
        ?int $replaceStart,
        ?int $replaceEnd
    ): string
    {
        $replaceStart = $replaceStart ?? 1;
        $lines = explode("\n", $existing);
        $newLines = [];
        $replacing = false;
        foreach ($lines as $offset => $line) {
            $lineNb = $offset + 1;
            if ($lineNb === $replaceStart) {
                $newLines[] = $content;
                $replacing = true;
                continue;
            }

            if (null !== $replaceEnd && $replacing && $lineNb <= $replaceEnd) {
                continue;
            }

            $newLines[] = $line;
        }

        return implode("\n", $newLines);

    }

    private function readContents(Filesystem $filesystem, MarkdownSectionTask $task)
    {
        if (!$filesystem->exists($task->path())) {
            return '';
        }

        return $filesystem->getContents($task->path());
    }
}
