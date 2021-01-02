<?php

namespace Maestro\Markdown\Task;

use Amp\Promise;
use League\CommonMark\Block\Element\Heading;
use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\Task\Context;
use Maestro\Core\Task\Exception\TaskError;
use Maestro\Core\Task\Handler;
use Maestro\Core\Task\Task;
use Twig\Environment as TwigEnvironment;
use function Amp\call;

class MarkdownSectionHandler implements Handler
{
    public function __construct(private TwigEnvironment $twig)
    {
    }

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

                if ($childNode->getLevel() !== $replaceHeader->getLevel()) {
                    continue;
                }

                if ($replaceStart) {
                    $replaceEnd = $childNode->getStartLine() - 1;
                    break;
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
                $this->resolveContent($context, $task),
                $task->prepend(),
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
        bool $prepend,
        ?int $replaceStart,
        ?int $replaceEnd
    ): string {
        $replaceStart = $replaceStart ?? null;

        $lines = $existing === '' ? [] : explode("\n", $existing);
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

        if (null === $replaceStart && $prepend) {
            array_unshift($newLines, $content);
        }
        
        if (null === $replaceStart && !$prepend) {
            $newLines[] = $content;
        }

        return implode("\n", $newLines);
    }

    private function readContents(Filesystem $filesystem, MarkdownSectionTask $task): string
    {
        if (!$filesystem->exists($task->path())) {
            return '';
        }

        return $filesystem->getContents($task->path());
    }

    private function resolveContent(Context $context, MarkdownSectionTask $task): string
    {
        $template = $task->template();
        $content = $task->content();

        if (null === $content && null === $template) {
            return '';
        }

        if ($content && $template) {
            throw new TaskError(sprintf(
                'You cannot provide both `content` and `template` (got template "%s")',
                $template
            ));
        }

        if ($content) {
            return $content;
        }

        if (!$template) {
            throw new TaskError(sprintf(
                'You have not provided `content`, you must provide a `template`'
            ));
        }

        return $this->twig->render(
            $template,
            $task->vars()
        );
    }
}
