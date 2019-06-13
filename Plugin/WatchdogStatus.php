<?php
namespace Creatuity\CronRunner\Plugin;

use Creatuity\CronRunner\Model\CronJob;

class WatchdogStatus
{

    /** @var \Creatuity\CronRunner\Model\Resource\Jobs\Collection */
    protected $jobs;


	public function __construct(
		\Creatuity\CronRunner\Model\Resource\Jobs\Collection $jobs
	) {
		$this->jobs = $jobs;
	}

    public function afterGetStatus(\Creatuity\Monitor\Api\Data\Status $subject, $result)
    {
        foreach ($this->jobs->getItems() as $job) {
            $result['job:' . $job->getGroupId() . ':' .  $job->getJobName()] = !$job->getSettingEnabled() || $job->getStatus() != CronJob::STATUS_ERROR;
        }
        return $result;
    }
}