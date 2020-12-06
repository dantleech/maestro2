<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Amp\Success;
use Maestro2\Core\Path\PathResolver;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TemplateHandler implements Handler
{
    public function __construct(private PathResolver $pathResolver, private Environment $twig)
    {
    }

    public static function createForBasePath(string $basePath): self
    {
        return new self(new PathResolver($basePath), new Environment(new FilesystemLoader($basePath), [
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
