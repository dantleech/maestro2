<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Amp\Success;
use Maestro2\Core\Exception\RuntimeException;
use Maestro2\Core\Path\WorkspacePathResolver;
use Maestro2\Core\Report\Publisher\NullPublisher;
use Maestro2\Core\Report\Report;
use Maestro2\Core\Report\ReportPublisher;
use Maestro2\Core\Util\PermissionUtil;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TemplateHandler implements Handler
{
    private ReportPublisher $publisher;

    public function __construct(private WorkspacePathResolver $pathResolver, private Environment $twig, ?ReportPublisher $publisher = null)
    {
        $this->publisher = $publisher ?: new NullPublisher();
    }

    public static function createForBasePath(string $basePath): self
    {
        return new self(new WorkspacePathResolver($basePath), new Environment(new FilesystemLoader($basePath), [
            'strict_variables' => true,
        ]));
    }

    public function taskFqn(): string
    {
        return TemplateTask::class;
    }

    public function run(Task $task): Promise
    {
        assert($task instanceof TemplateTask);
        (function (string $path) use ($task) {
            if (!$task->overwrite() && file_exists($path)) {
                return;
            }

            (static function (string $dir, int $mode): void {
                if (file_exists($dir)) {
                    return;
                }

                if (@mkdir($dir, 0744, true)) {
                    return;
                }

                throw new RuntimeException(sprintf(
                    'Could not create directory "%s"',
                    $dir
                ));
            })(dirname($path), $task->mode());

            file_put_contents(
                $path,
                $this->twig->render(
                    $task->template(),
                    $task->vars()
                )
            );
            chmod($path, $task->mode());

            // required?
            clearstatcache(true);

            $this->publisher->publish(
                $task->group(),
                Report::ok(sprintf('Applied "%s" to "%s" (mode: %s)', $task->template(), $path, PermissionUtil::formatOctal($task->mode())))
            );
        })($this->pathResolver->resolve($task->target()));
        return new Success();
    }
}
