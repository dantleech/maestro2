<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Core\Exception\RuntimeException;
use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\Report\Report;
use Maestro\Core\Report\TaskReportPublisher;
use Maestro\Core\Util\PermissionUtil;
use Twig\Environment;
use Twig\Error\LoaderError;

class TemplateHandler implements Handler
{
    public function __construct(
        private Environment $twig
    ) {
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

            try {
                $filesystem->putContents(
                    $task->target(),
                    $this->twig->render(
                        $task->template(),
                        $task->vars()
                    )
                );
            } catch (LoaderError $error) {
                throw new RuntimeException($error->getMessage(), 0, $error);
            }

            $filesystem->setMode($task->target(), $task->mode());
            $context->service(TaskReportPublisher::class)->publish(
                Report::ok(sprintf(
                    'Applied "%s" to "%s" (mode: %s)',
                    $task->template(),
                    $task->target(),
                    PermissionUtil::formatOctal($task->mode())
                ))
            );
        })(
            $context->service(Filesystem::class)
        );

        return new Success($context);
    }
}
