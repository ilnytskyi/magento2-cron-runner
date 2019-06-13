<?php
/**
 * Copyright © 2015 Creatuity. All rights reserved.
 */

namespace Creatuity\CronRunner\Model\Resource\Jobs;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Creatuity\CronRunner\Model\Jobs', 'Creatuity\CronRunner\Model\Resource\Jobs');
    }
}
