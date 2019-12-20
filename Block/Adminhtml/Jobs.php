<?php
namespace Fsw\CronRunner\Block\Adminhtml;

class Jobs extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'jobs';
        $this->_headerText = __('Cron Jobs');
        $this->_addButtonLabel = null;
        parent::_construct();
        $this->buttonList->remove('add');
    }
    
}
