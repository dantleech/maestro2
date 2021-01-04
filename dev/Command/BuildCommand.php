<?php

namespace Maestro\Development\Command;

use Maestro\Development\TaskCompiler;
use Maestro\Development\TaskDocBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCommand extends Command
{
    const ARG_PATH = 'path';

    public function __construct(private TaskCompiler $compiler)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('build');
        $this->addArgument(self::ARG_PATH, InputArgument::OPTIONAL, 'Specific path');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getArgument(self::ARG_PATH);
        assert(is_string($path)||is_null($path));
        $output->writeln('Generating task documentation ...');
        $this->compiler->build($path);
        $output->writeln('... done');
        return 0;
    }

}
