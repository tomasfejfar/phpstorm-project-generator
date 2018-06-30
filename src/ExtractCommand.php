<?php

declare(strict_types=1);

namespace PhpStormGen;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExtractCommand extends Command
{
    const NAME = 'extract';

    protected function configure()
    {
        $this->setName(self::NAME);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>PhpStorm project generator</info>');
    }
}
