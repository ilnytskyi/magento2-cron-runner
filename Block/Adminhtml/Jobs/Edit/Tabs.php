<?php
/**
 * Copyright © 2015 Creatuity. All rights reserved.
 */
namespace Creatuity\CronRunner\Block\Adminhtml\Jobs\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('creatuity_cron_jobs_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Item'));
    }
}
