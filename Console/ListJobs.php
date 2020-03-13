<?php

namespace Fsw\CronRunner\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListJobs extends Base
{
    protected function configure()
    {
        $this->setName('fsw:cron:list')
            ->setDescription('List available cron jobs');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->listValidJobs($output);
    }
}