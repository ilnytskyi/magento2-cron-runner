<?php

namespace Fsw\CronRunner\Console;

use Fsw\CronRunner\Model\CronJobFactory;
use Fsw\CronRunner\Model\CronRepository;
use Fsw\CronRunner\Model\CronJob;
use Magento\Cron\Model\ConfigInterface;
use Magento\Cron\Model\Schedule;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Base extends Command
{
    const SPAWN_CHILD_LIMIT = 16;

    /** @var ConfigInterface */
    protected $config;

    /** @var ObjectManagerInterface */
    protected $objectManager;

    /** @var State */
    protected $state;

    /** @var AreaList */
    protected $areaList;

    /** @var Schedule */
    protected $_dummySchedule;

    /** @var CronJobFactory */
    protected $cronJobFactory;

    /** @var CronRepository */
    protected $cronRepository;

    /** @var CronJob[] */
    protected $jobs = [];

	public function __construct(
		CronJobFactory $cronJobFactory,
		Schedule $_dummySchedule,
		AreaList $areaList,
		State $state,
		ObjectManagerInterface $objectManager,
		CronRepository $cron,
		ConfigInterface $config,
		CronRepository $cronRepository,
		$name = NULL
	) {
		parent::__construct($name);
		$this->cronRepository = $cronRepository;
		$this->cronJobFactory = $cronJobFactory;
		$this->_dummySchedule = $_dummySchedule;
		$this->areaList = $areaList;
		$this->state = $state;
		$this->objectManager = $objectManager;
		$this->config = $config;
	}

    /**
     * @return array|CronJob[]
     */
    protected function getJobsToRun()
    {
        $groups = $this->config->getJobs();
        $queue = $this->cronRepository->getExecutionQueue();
        /** @var CronJob[] $jobs */
        $jobs = [];
        foreach ($groups as $groupId => $group) {
            foreach ($group as $jobName => $job) {
                $cronJob = $this->cronJobFactory->create($groupId, $jobName, $job, array_search($groupId . '.' . $jobName , $queue));
                if ($cronJob->isValid()) {
                    if ($cronJob->shouldBeExecuted(new \DateTime())) {
                        $jobs[] = $cronJob;
                    }
                }
            }
        }
        usort($jobs, function ($a, $b) { return $a->priority - $b->priority; });
        return $jobs;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function setAreaCode()
    {
        $this->state->setAreaCode(Area::AREA_CRONTAB);
        $configLoader = $this->objectManager->get(ConfigLoaderInterface::class);
        $this->objectManager->configure($configLoader->load(Area::AREA_CRONTAB));
        $this->areaList->getArea(Area::AREA_CRONTAB)->load(Area::PART_TRANSLATE);
    }

    /**
     * @param $job CronJob
     * @return bool true if this is child process / false if this is parent or no process was spawned
     */
    protected function spawnChild($job)
    {
        if (count($this->jobs) >= static::SPAWN_CHILD_LIMIT) {
            return false;
        }
        if ($childPid = pcntl_fork()) { //this is the parent process
            $this->jobs[$childPid] = $job;
            $job->markAsStarted($childPid);
            return false;
        } else {
            return true;
        }
    }

    /**
     * @throws \Exception
     */
    protected function waitForAllChildren()
    {
        while (count($this->jobs)) {
            $pid = pcntl_wait($status, 0, $rusage);
            if ($pid == -1) {
                throw new \Exception('critical');
            }

            $this->jobs[$pid]->markAsFinished(
                $pid,
                pcntl_wifexited($status) ? pcntl_wexitstatus($status) : -1,
                $rusage
            );
            unset($this->jobs[$pid]);
        }
    }

    /**
     * @param CronJob $job
     */
    public function executeSingeJob(CronJob $job, $silent = true)
    {
        $job->markAsStarted(getmypid());
        $ret = $this->executeJob($job, $silent);
        $job->markAsFinished(getmypid(), $ret, getrusage());
    }

    /**
     * @param $job CronJob
     * @return int
     */
    protected function executeJob($job, $silent = true)
    {
        if ($silent) {
            if ($limit = $job->getMemoryLimit()) {
                ini_set('memory_limit', $limit . 'M');
            }

            if ($limit = $job->getTimeLimit()) {
                set_time_limit($limit);
            }

            ob_start(function ($string) use ($job) {
                if (!empty(trim($string))) {
                    $job->setOutput($string);
                }
                return "";
            }, 2048);
        }
        $ret = 0;
        try {
            call_user_func([$this->objectManager->create($job->instance), $job->method], $this->_dummySchedule);
        } catch (\Throwable $e) {
            $this->handleException($e);
            $job->setError($e->getMessage());
            if (!$silent) echo $e->getMessage();
            //force process to return error:
            $ret = 1;
        }
        if ($silent) {
            ob_end_flush();
        }
        return $ret;
    }

    /**
     * @param \Throwable $exception
     */
    public function handleException(\Throwable $exception)
    {
        //
    }

    /**
     * @param OutputInterface $output
     */
    public function listValidJobs(OutputInterface $output)
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