<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Amp\Success;
use Maestro2\Core\Exception\RuntimeException;
use Maestro2\Core\Path\WorkspacePathResolver;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TemplateHandler implements Handler
{
    public function __construct(private WorkspacePathResolver $pathResolver, private Environment $twig)
    {
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
        })($this->pathResolver->resolve($task->target()));
        return new Success();
    }
}
