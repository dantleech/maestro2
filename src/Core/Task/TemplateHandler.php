<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Core\Exception\RuntimeException;
use Maestro\Core\Fact\CwdFact;
use Maestro\Core\Fact\GroupFact;
use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\Report\Publisher\NullPublisher;
use Maestro\Core\Report\Report;
use Maestro\Core\Report\ReportPublisher;
use Maestro\Core\Util\PermissionUtil;
use Twig\Environment;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use Webmozart\PathUtil\Path;

class TemplateHandler implements Handler
{
    private ReportPublisher $publisher;

    public function __construct(
        private Filesystem $filesystem,
        private Environment $twig,
        ?ReportPublisher $publisher = null
    ) {
        $this->publisher = $publisher ?: new NullPublisher();
    }

    public function taskFqn(): string
    {
        return TemplateTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof TemplateTask);
        (function (Filesystem $filesystem) use ($task, $context) {
            if (!$task->overwrite() && $filesystem->exists($task->target())) {
                return;
            }

            (function (string $dir, int $mode) use ($filesystem): void {
                if ($filesystem->exists($dir)) {
                    return;
                }

                $filesystem->createDirectory($dir, 0744);
            })(dirname($task->target()), $task->mode());

            $filesystem->putContents(
                $task->target(),
                $this->twig->render(
                    $task->template(),
                    $task->vars()
                )
            );
            $filesystem->setMode($task->target(), $task->mode());
            $this->publisher->publish(
                $context->factOrNull(GroupFact::class)?->group() ?: 'template',
                Report::ok(sprintf(
                    'Applied "%s" to "%s" (mode: %s)',
                    $task->template(),
                    $task->target(),
                    PermissionUtil::formatOctal($task->mode())
                ))
            );
        })(
            $this->filesystem->cd(
                $context->factOrNull(CwdFact::class)?->cwd() ?: '/'
            ),
        );

        return new Success($context);
    }
}
