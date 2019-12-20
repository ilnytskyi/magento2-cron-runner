<?php

namespace Fsw\CronRunner\Controller\Adminhtml\Jobs;

use Fsw\CronRunner\Model\Jobs;

class Save extends \Fsw\CronRunner\Controller\Adminhtml\Jobs
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $model = $this->_objectManager->create('Fsw\CronRunner\Model\Jobs');
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
                $session = $this->_objectManager->get('Magento\Backend\Model\Session');

                /** @var $model Jobs */
                if ($this->getRequest()->getParam('clear_stats')) {
                    $model->setData([
                        'id' => $id,
                        'stats_started' => 0,
                        'stats_finished_error' => 0,
                        'stats_finished_ok' => 0,
                        'stats_last_duration' => 0,
                        'stats_avg_duration' => 0,
                        'stats_last_memory' => 0,
                        'stats_avg_memory' => 0,
                    ]);
                    $model->save();
                    $this->messageManager->addSuccess(__('Stats cleared'));
                } elseif ($this->getRequest()->getParam('force_run_now')) {
                    $model->setData([
                        'id' => $id,
                        'force_run_flag' => 1
                    ]);
                    $model->save();
                    $this->messageManager->addSuccess(__('Job will be executed within 60s'));
                } else {
                    $model->setData($data);
                    $model->setData('setting_enabled', !empty($data['setting_enabled']));

                    $session->setPageData($model->getData());

                    $model->save();
                    $this->messageManager->addSuccess(__('You saved the job.'));
                }
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('fsw_cron/*/edit', ['id' => $model->getId()]);
                    return;
                }
                $this->_redirect('fsw_cron/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('fsw_cron/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('fsw_cron/*/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the job data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('fsw_cron/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->_redirect('fsw_cron/*/');
    }
}
