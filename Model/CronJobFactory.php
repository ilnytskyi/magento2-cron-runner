<?php

namespace Creatuity\CronRunner\Model;


use Magento\Framework\ObjectManagerInterface;

class CronJobFactory
{

    /** @var ObjectManagerInterface */
    protected $objectManager;

    /**
     * JobFactory constructor.
     * @param ObjectManagerInterface $objectManager
     */
	public function __construct(
		ObjectManagerInterface $objectManager
	) {
		$this->objectManager = $objectManager;
	}

    /**
     * @param $groupId
     * @param $jobName
     * @param $config
     * @param $priority
     * @return CronJob|bool
     */
    function create($groupId, $jobName, $config, $priority)
    {
        return $this->objectManager->create(CronJob::class, [
            'groupId' => $groupId,
            'jobName' => $jobName,
            'schedule' => isset($config['schedule']) ? $config['schedule'] : null,
            'instance' => isset($config['instance']) ? $config['instance'] : null,
            'method' => isset($config['method']) ? $config['method'] : null,
            'priority' => $priority
        ]);
    }
}