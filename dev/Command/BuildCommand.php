<?php

namespace Maestro\Development\Command;

use Maestro\Development\TaskDocBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCommand extends Command
{
    public function __construct(private TaskDocBuilder $builder)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('build');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Generating task documentation ...');
        $this->builder->build();
        $output->writeln('... done');
        return 0;
    }

}