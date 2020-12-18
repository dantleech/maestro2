<?php

namespace Maestro2\Core\Extension\Command;

use Amp\Loop;
use Maestro2\Core\Exception\RuntimeException;
use Maestro2\Core\Report\Report;
use Maestro2\Core\Report\ReportProvider;
use Maestro2\Maestro;
use Symfony\Component\Console\Command\Command;
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
    const OPT_REPORT_LEVEL = 'report-level';

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
        $this->addArgument(self::ARG_PIPELINE, InputArgument::REQUIRED, 'Pipeline name');
        $this->addOption(self::OPT_REPO, null, InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, 'Include this repository');
        $this->addOption(self::OPT_REPORT_LEVEL, null, InputOption::VALUE_REQUIRED, 'Report level (error, warn, info, ok)', Report::LEVEL_OK);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pipeline = (static function (mixed $pipeline): string {
            if (!is_string($pipeline)) {
                throw new RuntimeException(
                    'Invalid pipeline type'
                );
            }

            return $pipeline;
        })($input->getArgument(self::ARG_PIPELINE));
        $reportLevel = $input->getOption(self::OPT_REPORT_LEVEL);
        Assert::string($reportLevel, 'Report level must be a string');

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
        Loop::run(function () use ($input, $pipeline) {
            yield $this->maestro->run(
                pipeline: $pipeline,
                repos: (array)$input->getOption(self::OPT_REPO)
            );
        });
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
}
