<?php

namespace Fsw\CronRunner\Model;


use Magento\Framework\App\ResourceConnection;

class CronRepository
{
    /** @var ResourceConnection */
    private $resourceConnection;

    /**
     * Cron constructor.
     * @param ResourceConnection $resourceConnection
     */
	public function __construct(
		ResourceConnection $resourceConnection
	) {
		$this->resourceConnection = $resourceConnection;
	}

    /**
     * @return array
     */
    public function getExecutionQueue()
    {
        $connection = $this->resourceConnection->getConnection();
        $ret = $connection->fetchCol($connection->select()
            ->from('fsw_cron', [new \Zend_Db_Expr('CONCAT(group_id, ".", job_name)')])
            ->order(['force_run_flag DESC', 'started_at ASC']));
        $this->resourceConnection->closeConnection();
        return $ret;
    }

}
