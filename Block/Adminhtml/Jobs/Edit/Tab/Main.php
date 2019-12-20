<?php
// @codingStandardsIgnoreFile

namespace Fsw\CronRunner\Block\Adminhtml\Jobs\Edit\Tab;


use Fsw\CronRunner\Model\Jobs;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;



class Main extends Generic implements TabInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Job Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Job Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return Jobs
     */
    public function getJob()
    {
        return $this->_coreRegistry->registry('current_fsw_cron_jobs');
    }
}
