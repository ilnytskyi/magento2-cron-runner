<?php

namespace Fsw\CronRunner\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Execute extends Base
{
    protected function configure()
    {
        $this->setName('fsw:cron:execute')
            ->addOption('group', null, InputOption::VALUE_REQUIRED, 'Group id, if set will run only jobs from this group')
            ->addOption('job', null, InputOption::VALUE_REQUIRED, 'Job name, use this to force given job')
            ->setDescription('Execute single cron task synchronously');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $groupId = $input->getOption('group');
        $jobName = $input->getOption('job');

        $groups = $this->config->getJobs();

        if (!isset($groups[$groupId])) {
            throw new \Exception('unknown group id. use fsw:cron:list to see available options');
        }

        if (!isset($groups[$groupId][$jobName])) {
            throw new \Exception('unknown job name. use fsw:cron:list to see available options');
        }

        $cronJob = $this->cronJobFactory->create($groupId, $jobName, $groups[$groupId][$jobName], 0);

        $this->setAreaCode();

        $this->executeSingeJob($cronJob);
    }
}