<?php

namespace Creatuity\CronRunner\Block\Adminhtml;

use Creatuity\CronRunner\Model\Jobs;
use Magento\Backend\Block\Widget\Grid\ColumnSet;

class JobsColumns extends ColumnSet
{
    /**
     * @param $row Jobs
     * @return string
     */
    public function getRowClass($row) {
        return 'job-status-' . ($row->getSettingEnabled() ? $row->getStatus() : 'disabled');
    }
}