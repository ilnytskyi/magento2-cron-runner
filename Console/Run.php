<?php

namespace Creatuity\CronRunner\Console;

use Creatuity\CronRunner\Model\CronJobFactory;
use Creatuity\CronRunner\Model\CronRepository;
use Creatuity\CronRunner\Model\CronJob;
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

class Run extends Command
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

    protected function configure()
    {
        $this->setName('creatuity:cron:run')
            ->addOption('group', null, InputOption::VALUE_OPTIONAL, 'Group id, if set will run only jobs from this group')
            ->addOption('job', null, InputOption::VALUE_OPTIONAL, 'Job name, use this to force given job')
            ->setDescription('Alternative, simplified cron manager');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setAreaCode();

        $jobs = $this->getJobsToRun($input->getOption('group'), $input->getOption('job'));

        foreach ($jobs as $job) {
            if ($this->spawnChild($job)) {
                $this->executeJob($job);
                return;
            }
        }

        $this->waitForAllChildren();
    }

    /**
     * @param $paramGroupId
     * @param $paramJobName
     * @return array|CronJob[]
     * @throws \Exception
     */
    protected function getJobsToRun($paramGroupId, $paramJobName)
    {
        $groups = $this->config->getJobs();

        if ($paramGroupId !== null && !isset($groups[$paramGroupId])) {
            throw new \Exception('unknown group id');
        }
        if ($paramJobName !== null && !isset($groups[$paramGroupId][$paramJobName])) {
            throw new \Exception('unknown job name');
        }

        $queue = $this->cronRepository->getExecutionQueue();
        /** @var CronJob[] $jobs */
        $jobs = [];
        foreach ($groups as $groupId => $group) {
            if ($paramGroupId !== null && $paramGroupId !== $groupId) {
                continue;
            }
            foreach ($group as $jobName => $job) {
                if ($paramJobName !== null && $paramJobName !== $jobName) {
                    continue;
                }
                $cronJob = $this->cronJobFactory->create($groupId, $jobName, $job, array_search($groupId . '.' . $jobName , $queue));
                if ($cronJob->isValid()) {
                    if ($paramJobName !== null || $cronJob->shouldBeExecuted(new \DateTime())) {
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
     *
     */
    protected function waitForAllChildren()
    {
        while (count($this->jobs)) {
            $pid = pcntl_wait($status, 0, $rusage);
            if ($pid == -1) {
                throw new \Exception('critical');
            }
            $time = 0;
            isset($rusage["ru_utime.tv_sec"]) && $time += $rusage["ru_utime.tv_sec"] * 1000;
            isset($rusage["ru_utime.tv_usec"]) && $time += (int)($rusage["ru_utime.tv_usec"] / 1000);
            isset($rusage["ru_stime.tv_sec"]) && $time += $rusage["ru_stime.tv_sec"] * 1000;
            isset($rusage["ru_stime.tv_usec"]) && $time += (int)($rusage["ru_stime.tv_usec"] / 1000);
            $mem = empty($rusage['ru_maxrss']) ? 0 : $rusage['ru_maxrss'];

            $this->jobs[$pid]->markAsFinished(
                $pid,
                pcntl_wifexited($status) ? pcntl_wexitstatus($status) : -1,
                $time,
                $mem
            );
            unset($this->jobs[$pid]);
        }
    }

    /**
     * @param $job CronJob
     */
    protected function executeJob($job)
    {
        if ($limit = $job->getMemoryLimit()) {
            ini_set('memory_limit', $limit . 'M');
        }

        if ($limit = $job->getTimeLimit()) {
            set_time_limit($limit);
        }

        ob_start(function($string) use($job) {
            if (!empty(trim($string))) {
                $job->setOutput($string);
            }
            return "";
        }, 2048);
        $ret = 0;
        try {
            call_user_func([$this->objectManager->create($job->instance), $job->method], $this->_dummySchedule);
        } catch (\Throwable $e) {
            $job->setError($e->getMessage());
            //force process to return error:
            $ret = 1;
        }
        ob_end_flush();
        exit ($ret);
    }

}