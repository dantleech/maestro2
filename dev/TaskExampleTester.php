<?php

namespace Maestro\Development;

use Maestro\Core\Exception\RuntimeException;
use Maestro\Core\Queue\Enqueuer;
use Maestro\Core\Queue\TaskRunner;
use Maestro\Core\Task\Context;
use Maestro\Core\Task\ContextFactory;
use Maestro\Core\Task\Exception\TaskError;
use Maestro\Core\Task\TaskContext;
use Psr\Log\LoggerInterface;
use function Amp\Promise\wait;
use function fwrite;
use function tempnam;
use function tmpfile;

final class TaskExampleTester
{
    public function __construct(
        private Enqueuer $runner,
        private ContextFactory $contextFactory,
        private LoggerInterface $logger
    )
    {
    }
    public function test(TaskMetadata $taskMeta): void
    {
        foreach ($taskMeta->examples() as $example) {
            if ($example->type() !== 'task') {
                continue;
            }

            $imports = [
                "Maestro\Core\Task\Context",
                "Maestro\Core\Task\NullTask",
                "Maestro\Core\Task\SequentialTask",
                "Maestro\Core\Task\SetDirectoryTask",
            ];

            if (!in_array($taskMeta->namespacedName(), $imports)) {
                $imports[] = $taskMeta->namespacedName();
            }
            $imports = implode("\n", array_map(function (string $import) {
                return 'use ' . $import . ';';
            }, $imports));
            $template = <<<EOT
<?php

{$imports};

return {$example->exampe()};
EOT
            ;
            $fname = tempnam(sys_get_temp_dir(), 'maestro-doc-test');
            file_put_contents($fname, $template);
            $task = require ($fname);
            unlink($fname);
            try {
                wait(
                    $this->runner->enqueue(
                        new TaskContext($task, $this->contextFactory->createContext())
                    )
                );
            } catch (RuntimeException $error) {
                $this->logger->warning(sprintf(
                    'Example "%s" with task "%s": %s',
                    $taskMeta->name(),
                    get_class($task),
                    $error->getMessage()
                ));
            }
        }



    }
}
