<?php

namespace Fsw\CronRunner\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunAll extends Base
{
    protected function configure()
    {
        $this->setName('fsw:cron:run')
            ->setDescription('This command is automaticially plugged to replace cron:run');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->runAllJobs();
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function runAllJobs()
    {
        $this->setAreaCode();
        $jobs = $this->getJobsToRun();
        foreach ($jobs as $job) {
            if (!function_exists('pcntl_fork')) {
                //run single job if no pcntl module installed
                return $this->executeSingeJob($job);
            }
            if ($this->spawnChild($job)) {
                exit($this->executeJob($job));
            }
        }
        $this->waitForAllChildren();
    }

}