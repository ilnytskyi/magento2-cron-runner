<?php

namespace Fsw\CronRunner\Block\Adminhtml\Jobs\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Settings extends Generic implements TabInterface
{

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Job Settings');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Job Settings');
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
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_fsw_cron_jobs');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('job_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Job Settings')]);
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField(
            'setting_enabled',
            'checkbox',
            [
                'name' => 'setting_enabled',
                'label' => __('Enabled'),
                'title' => __('Enabled'),
                'onclick'   => 'this.value = this.checked ? 1 : 0;',
            ]
        );
        $fieldset->addField(
            'setting_memorylimit',
            'text',
            ['name' => 'setting_memorylimit', 'label' => __('Memory Limit (MB)'), 'title' => __('Memory Limit')]
        );
        $fieldset->addField(
            'setting_timelimit',
            'text',
            ['name' => 'setting_timelimit', 'label' => __('Time Limit (s)'), 'title' => __('Time Limit')]
        );
        $fieldset->addField(
            'setting_schedule',
            'text',
            ['name' => 'setting_schedule', 'label' => __('Schedule Override'), 'title' => __('Schedule Override')]
        );

        $form->setValues($model->getData());
        $form->getElement('setting_enabled')->setIsChecked(!empty($model->getData('setting_enabled')));

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
