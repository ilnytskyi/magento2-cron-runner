<?php

namespace Fsw\CronRunner\Model\Resource;

class Jobs extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('fsw_cron', 'id');
    }
}
