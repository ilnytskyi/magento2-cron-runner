<?php
/**
 * Copyright © 2015 Creatuity. All rights reserved.
 */
namespace Creatuity\CronRunner\Block\Adminhtml;

class JobsGrid extends \Magento\Backend\Block\Widget\Grid
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setDefaultSort('status');
        $this->setDefaultDir('asc');
    }

    protected function _preparePage()
    {
        $this->getCollection()->setPageSize(null);
        $this->getCollection()->setCurPage((int)$this->getParam($this->getVarNamePage(), $this->_defaultPage));
    }

    public function getPagerVisibility() {
        return false;
    }

}
