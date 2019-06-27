<?php
/**
 * Copyright © 2015 Creatuity. All rights reserved.
 */

namespace Creatuity\CronRunner\Controller\Adminhtml\Jobs;

class Index extends \Creatuity\CronRunner\Controller\Adminhtml\Jobs
{
    /**
     * Jobs list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Creatuity_CronRunner::cron');
        $resultPage->getConfig()->getTitle()->prepend(__('Cron Jobs'));
        $resultPage->addBreadcrumb(__('Creatuity'), __('Creatuity'));
        $resultPage->addBreadcrumb(__('Cron Jobs'), __('Cron Jobs'));
        return $resultPage;
    }
}
