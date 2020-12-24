<?php

namespace Maestro\Core\Extension\Command;

use Amp\Loop;
use Maestro\Core\Report\Report;
use Maestro\Core\Report\ReportProvider;
use Maestro\Core\Report\Table;
use Maestro\Maestro;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table as SymfonyTable;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use Webmozart\Assert\Assert;

class RunCommand extends Command
{
    private const NAME = 'run';

    private const ARG_PIPELINE = 'pipeline';

    private const OPT_REPO = 'repo';
    private const OPT_REPORT_LEVEL = 'report-level';
    const OPT_BRANCH = 'branch';

    public function __construct(
        private Maestro $maestro,
        private ReportProvider $reportProvider
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::NAME);
        $this->setDescription('Run a pipeline');
        $this->addArgument(self::ARG_PIPELINE, InputArgument::OPTIONAL, 'Pipeline name');
        $this->addOption(self::OPT_REPO, null, InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, 'Include this repository');
        $this->addOption(self::OPT_REPORT_LEVEL, null, InputOption::VALUE_REQUIRED, 'Report level (error, warn, info, ok)', Report::LEVEL_OK);
        $this->addOption(self::OPT_BRANCH, null, InputOption::VALUE_REQUIRED, 'Branch to operate on');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pipeline = Cast::stringOrNull($input->getArgument(self::ARG_PIPELINE));
        $reportLevel = Cast::string($input->getOption(self::OPT_REPORT_LEVEL));
        $branch = Cast::stringOrNull($input->getOption(self::OPT_BRANCH));

        if (null === $pipeline) {
            $this->suggestPipelineCreation($output);
            return 1;
        }

        Loop::setErrorHandler(function (Throwable $error) use ($output) {
            $output->writeln(sprintf('<error>%s</>', $error->getMessage()));
            $output->writeln($error->getTraceAsString());

            while ($previous = $error->getPrevious()) {
                $output->writeln(sprintf('Previous <error>%s</>', $previous->getMessage()));
                $output->writeln($previous->getTraceAsString());
                $error = $previous;
            }
        });

        $start = microtime(true);

        $this->maestro->run(
            pipeline: $pipeline,
            repos: (array)$input->getOption(self::OPT_REPO)
        );

        $duration = microtime(true) - $start;

        $style = new SymfonyStyle($input, $output);

        $output->writeln('');
        $reports = $this->reportProvider->groups();
        foreach ($reports as $group) {
            $reportsForLevel = $group->reports()->forLevel($reportLevel);
            if ($reportsForLevel->count() === 0) {
                continue;
            }
            $style->section($group->name());
            foreach ($reportsForLevel as $report) {
                assert($report instanceof Report);
                $output->writeln(sprintf(
                    "  %s %s",
                    match ($report->level()) {
                        Report::LEVEL_OK => '<fg=green>✔</>',
                        Report::LEVEL_WARN => '<fg=yellow>⚠</>',
                        Report::LEVEL_FAIL => '<fg=red>✘</>',
                        Report::LEVEL_INFO => '<fg=blue>i</>',
                    },
                    $report->title()
                ));
                if ($report->body()) {
                    $style->block(
                        trim($report->body()),
                        escape: false,
                        padding: true,
                        prefix: '    ',
                    );
                }
            }
            $output->writeln('');
        }

        if ($this->reportProvider->table()->rows()) {
            $this->renderTable($output, $this->reportProvider->table());
        }

        (function (int $total, int $infos, int $warns, int $fails) use ($output, $duration) {
            $output->writeln(sprintf(
                '<%s;options=bold>%s reports, %s informations, %s warnings, %s failed in %ss</>',
                $fails > 0 ? 'bg=red;fg=white' : ($warns > 0 ? 'bg=yellow;fg=black' : 'bg=green;fg=black'),
                $total,
                $infos,
                $warns,
                $fails,
                number_format($duration, 2),
            ));
        })(
            $reports->reports()->count(),
            $reports->reports()->infos()->count(),
            $reports->reports()->warns()->count(),
            $reports->reports()->fails()->count(),
        );

        return 0;
    }

    private function renderTable(OutputInterface $output, Table $table): void
    {
        $consoleTable = new SymfonyTable($output);
        $consoleTable->setHeaders($table->headers());

        foreach ($table->rows() as $row) {
            $consoleTable->addRow($row);
        }

        $consoleTable->render();
    }

    private function suggestPipelineCreation(OutputInterface $output): void
    {
        $pipeline = file_get_contents(__DIR__ . '/../../../../example/pipeline/EmptyPipeline.php');
        $output->writeln(
            <<<EOT
You must specify the path to a pipeline file, for example:

# pipeline/MyPipeline.php

{$pipeline}
EOT
        );
    }
}
