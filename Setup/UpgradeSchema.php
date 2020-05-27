<?php

namespace Fsw\CronRunner\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.2.5') < 0) {
            $connection = $setup->getConnection();
            $connection->addColumn(
                $setup->getTable('fsw_cron'),
                'setting_schedule',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Override cron job schedule, used instead of schedule when set'
                ]
            );
        }
    }
}
