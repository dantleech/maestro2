<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Amp\Success;
use Maestro2\Core\Exception\RuntimeException;
use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Fact\GroupFact;
use Maestro2\Core\Filesystem\Filesystem;
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
        private Filesystem $filesystem,
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
                new Filesystem($basePath),
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

            if (Path::isAbsolute($task->template())) {
                $this->arrayLoader->setTemplate($task->template(), file_get_contents($task->template()));
            }

            $filesystem->putContents(
                $task->target(),
                $this->twig->render(
                    $task->template(),
                    $task->vars()
                )
            );
            $filesystem->setMode($task->target(), $task->mode());
            $this->publisher->publish(
                $task->group() ?: $context->fact(GroupFact::class)->group(),
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
