<?php
namespace Fsw\CronRunner\Block\Adminhtml\Jobs;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize form
     * Add standard buttons
     * Add "Save and Continue" button
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_jobs';
        $this->_blockGroup = 'Fsw_CronRunner';

        parent::_construct();

        $this->buttonList->add(
            'save_and_continue_edit',
            [
                'class' => 'save',
                'label' => __('Save and Continue Edit'),
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ]
            ],
            10
        );

        $this->buttonList->add(
            'clear_stats',
            [
                'class' => 'save',
                'label' => __('Clear Stats'),
                'data_attribute' => [
                    'mage-init' => ['button' => [
                        'event' => 'saveAndContinueEdit',
                        'target' => '#edit_form',
                        'eventData' => ['action' => ['args' => ['clear_stats' => '1']]],
                    ]],
                ]

            ],
            10
        );

        $this->buttonList->add(
            'force_run_now',
            [
                'class' => 'save',
                'label' => __('Run Now'),
                'data_attribute' => [
                    'mage-init' => ['button' => [
                        'event' => 'saveAndContinueEdit',
                        'target' => '#edit_form',
                        'eventData' => ['action' => ['args' => ['force_run_now' => '1']]],
                    ]],
                ]

            ],
            10
        );

    }

    /**
     * Getter for form header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        $job = $this->_coreRegistry->registry('current_fsw_cron_jobs');
        if ($job->getId()) {
            return __("Edit Item '%1'", $this->escapeHtml($job->getName()));
        } else {
            return __('New Item');
        }
    }
}
