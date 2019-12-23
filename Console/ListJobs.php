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
        $groups = $this->config->getJobs();
        foreach ($groups as $groupId => $group) {
            foreach ($group as $jobName => $job) {
                $cronJob = $this->cronJobFactory->create($groupId, $jobName, $job, 0);
                if ($cronJob->isValid()) {
                    $output->writeln("$groupId $jobName");
                }
            }
        }
    }
}