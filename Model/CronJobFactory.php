<?php

namespace Fsw\CronRunner\Model;

use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;

class CronJobFactory
{
    /** @var ObjectManagerInterface */
    protected $objectManager;
    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /**
     * JobFactory constructor.
     * @param ObjectManagerInterface $objectManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
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
        if (!isset($config['schedule']) && isset($config['config_path'])) {
            $config['schedule'] = $this->scopeConfig->getValue(
                $config['config_path'],
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                0
            );
        }
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
