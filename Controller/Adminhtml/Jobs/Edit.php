<?php
/**
 * Copyright © 2015 Creatuity. All rights reserved.
 */

namespace Creatuity\CronRunner\Controller\Adminhtml\Jobs;

class Edit extends \Creatuity\CronRunner\Controller\Adminhtml\Jobs
{

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Creatuity\CronRunner\Model\Jobs');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This job no longer exists.'));
                $this->_redirect('creatuity_cron/*');
                return;
            }
        }
        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->_coreRegistry->register('current_creatuity_cron_jobs', $model);
        $this->_initAction();
        $this->_view->getLayout()->getBlock('jobs_jobs_edit');
        $this->_view->renderLayout();
    }
}
