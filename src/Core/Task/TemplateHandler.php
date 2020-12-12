<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Amp\Success;
use Maestro2\Core\Exception\RuntimeException;
use Maestro2\Core\Fact\GroupFact;
use Maestro2\Core\Path\WorkspacePathResolver;
use Maestro2\Core\Report\Publisher\NullPublisher;
use Maestro2\Core\Report\Report;
use Maestro2\Core\Report\ReportPublisher;
use Maestro2\Core\Util\PermissionUtil;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use Webmozart\PathUtil\Path;

class TemplateHandler implements Handler
{
    private ReportPublisher $publisher;

    public function __construct(
        private WorkspacePathResolver $pathResolver,
        private Environment $twig,
        private ArrayLoader $arrayLoader,
        ?ReportPublisher $publisher = null
    ) {
        $this->publisher = $publisher ?: new NullPublisher();
    }

    public static function createForBasePath(string $basePath): self
    {
        return (static function (string $basePath, ArrayLoader $arrayLoader) {
            return new self(
                new WorkspacePathResolver($basePath),
                new Environment(
                    new ChainLoader([
                        $arrayLoader,
                        new FilesystemLoader($basePath),
                    ]),
                    ['strict_variables' => true ]
                ),
                $arrayLoader
            );
        })($basePath, new ArrayLoader());
    }

    public function taskFqn(): string
    {
        return TemplateTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof TemplateTask);
        (function (string $path) use ($task, $context) {
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

            if (Path::isAbsolute($task->template())) {
                $this->arrayLoader->setTemplate($task->template(), file_get_contents($task->template()));
            }

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
                $task->group() ?: $context->fact(GroupFact::class)->group(),
                Report::ok(sprintf('Applied "%s" to "%s" (mode: %s)', $task->template(), $path, PermissionUtil::formatOctal($task->mode())))
            );
        })($this->pathResolver->resolve($task->target()));

        return new Success($context);
    }
}
