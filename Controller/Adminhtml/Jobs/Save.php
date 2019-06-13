<?php
/**
 * Copyright © 2015 Creatuity. All rights reserved.
 */

namespace Creatuity\CronRunner\Controller\Adminhtml\Jobs;

class Save extends \Creatuity\CronRunner\Controller\Adminhtml\Jobs
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $model = $this->_objectManager->create('Creatuity\CronRunner\Model\Jobs');
                $data = $this->getRequest()->getPostValue();
                $inputFilter = new \Zend_Filter_Input(
                    [],
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                $id = $this->getRequest()->getParam('id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong job is specified.'));
                    }
                }
                $model->setData($data);
                $model->setData('setting_enabled', !empty($data['setting_enabled']));

                $session = $this->_objectManager->get('Magento\Backend\Model\Session');
                $session->setPageData($model->getData());



                if ($this->getRequest()->getParam('clear_stats')) {
                    $model->clearStats();
                    $id && $model->load($id);
                    $this->messageManager->addSuccess(__('Stats cleared'));
                } else {
                    $model->save();
                    $this->messageManager->addSuccess(__('You saved the job.'));
                }
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('creatuity_cron/*/edit', ['id' => $model->getId()]);
                    return;
                }
                $this->_redirect('creatuity_cron/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('creatuity_cron/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('creatuity_cron/*/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the job data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('creatuity_cron/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->_redirect('creatuity_cron/*/');
    }
}
