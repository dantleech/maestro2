<?php

namespace Maestro2\Core\Extension\Command;

use Amp\Loop;
use Maestro2\Core\Report\Report;
use Maestro2\Core\Report\ReportProvider;
use Maestro2\Maestro;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class RunCommand extends Command
{
    const NAME = 'run';
    const ARG_TARGET = 'target';


    public function __construct(
        private Maestro $maestro,
        private ReportProvider $reportProvider
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::NAME);
        $this->addArgument(self::ARG_TARGET, InputArgument::IS_ARRAY, 'Targets to run');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        Loop::setErrorHandler(function (Throwable $error) use ($output) {
            $output->writeln(sprintf('<error>%s</>', $error->getMessage()));
        });
        Loop::run(function () use ($input) {
            yield $this->maestro->run($input->getArgument(self::ARG_TARGET));
        });

        foreach ($this->reportProvider->groups() as $group) {
            $output->writeln($group->name());
            $table = new Table($output);
            foreach ($group->reports() as $report) {
                $table->addRow([
                    match ($report->level()) {
                        Report::LEVEL_OK => '<info>OK!</info>',
                        Report::LEVEL_FAIL => '<error>NOK</error>',
                    },
                    $report->title()
                ]);
            }
            $table->render();
        }

        return 0;
    }
}
