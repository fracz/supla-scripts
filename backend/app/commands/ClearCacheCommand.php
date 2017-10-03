<?php

namespace suplascripts\app\commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCacheCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('cache:clear')
            ->setDescription('Clears the cache.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \FileSystemCache::invalidateGroup('');
        $output->writeln('Cache has been cleared successfully');
    }
}
