<?php

namespace Fsw\CronRunner\Controller\Adminhtml\Jobs;

class Edit extends \Fsw\CronRunner\Controller\Adminhtml\Jobs
{

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Fsw\CronRunner\Model\Jobs');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This job no longer exists.'));
                $this->_redirect('fsw_cron/*');
                return;
            }
        }
        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->_coreRegistry->register('current_fsw_cron_jobs', $model);
        $this->_initAction();
        $this->_view->getLayout()->getBlock('jobs_jobs_edit');
        $this->_view->renderLayout();
    }
}
