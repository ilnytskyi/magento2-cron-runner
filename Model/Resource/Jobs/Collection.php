<?php

namespace Fsw\CronRunner\Model\Resource\Jobs;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Fsw\CronRunner\Model\Jobs', 'Fsw\CronRunner\Model\Resource\Jobs');
    }
}
