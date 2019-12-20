<?php

namespace Fsw\CronRunner\Controller\Adminhtml\Jobs;

class Index extends \Fsw\CronRunner\Controller\Adminhtml\Jobs
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
        $resultPage->setActiveMenu('Fsw_CronRunner::cron');
        $resultPage->getConfig()->getTitle()->prepend(__('Cron Jobs'));
        $resultPage->addBreadcrumb(__('Cron Jobs'), __('Cron Jobs'));
        return $resultPage;
    }
}
