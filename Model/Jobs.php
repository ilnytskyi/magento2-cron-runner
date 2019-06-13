<?php
/**
 * Copyright © 2015 Creatuity. All rights reserved.
 */

namespace Creatuity\CronRunner\Model;

use Magento\Framework\App\ResourceConnection;

class Jobs extends \Magento\Framework\Model\AbstractModel
{

    /** @var ResourceConnection */
    private $resourceConnection;


	public function __construct(
		ResourceConnection $resourceConnection,
		\Magento\Framework\Model\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Framework\Model\ResourceModel\AbstractResource $resource = NULL,
		\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = NULL,
		array $data = []
	) {
		parent::__construct($context, $registry, $resource, $resourceCollection, $data);
		$this->resourceConnection = $resourceConnection;
	}

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Creatuity\CronRunner\Model\Resource\Jobs');
    }

    public function clearStats()
    {
        $this->resourceConnection->getConnection()->update('creatuity_cron', [
            'stats_started' => new \Zend_Db_Expr(0),
            'stats_finished_error' => new \Zend_Db_Expr(0),
            'stats_finished_ok' => new \Zend_Db_Expr(0),
            'stats_last_duration' => new \Zend_Db_Expr(0),
            'stats_avg_duration' => new \Zend_Db_Expr( 0),
            'stats_last_memory' => new \Zend_Db_Expr(0),
            'stats_avg_memory' => new \Zend_Db_Expr( 0),
        ], [
            'group_id = ?' => $this->getGroupId(),
            'job_name = ?' => $this->getJobName()
        ]);

        $this->load($this->getId());
    }

    public function formatTime($key)
    {
        $ms = $this->getData($key);
        $secs = floor($ms / 1000 % 60);
        $mins = floor($ms / (60 * 1000) % 60);
        $hours = floor($ms / (60 * 60 * 1000) % 60);
        $ms = floor($ms % 1000);

        return  ($hours ? $hours . 'h ' : '') .
                ($mins ? $mins . 'm ' : '') .
                $secs . '.' . sprintf('%03d', $ms) . 's';
    }

    public function formatMemory($key)
    {
        $kb = $this->getData($key);
        $units = ['KiB', 'MiB', 'GiB', 'TiB'];
        $unit = 0;
        while (($kb >= 1024) && ($unit < 3)) {
            $kb = floor($kb / 1024);
            $unit ++;
        }
        return $kb . ' ' . $units[$unit];
    }



}

