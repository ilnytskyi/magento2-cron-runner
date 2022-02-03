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
            ->addArgument('group', null, 'Group id')
            ->addArgument('job', null, 'Job name')
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
        $groupId = $input->getArgument('group');
        $jobName = $input->getArgument('job');

        $groups = $this->config->getJobs();

        if (!isset($groups[$groupId]) || !isset($groups[$groupId][$jobName])) {
            $output->writeln("unknown job. please use one of:");
            $this->listValidJobs($output);
            throw new \Exception('unknown job.');
        }

        $cronJob = $this->cronJobFactory->create($groupId, $jobName, $groups[$groupId][$jobName], 0);

        $this->setAreaCode();

        $this->executeSingeJob($cronJob, false);
    }
}
