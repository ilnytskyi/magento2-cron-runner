<?php

namespace Creatuity\CronRunner\Setup;

use Creatuity\Base\Setup\AbstractUpgradeData;
use Creatuity\Base\Setup\ModuleContextInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class UpgradeData
 * @package Creatuity\FFL\Setup
 */
class UpgradeData extends AbstractUpgradeData
{

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    protected function upgrade_1_0_0(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->report()->printMessage('Adding cron table');


        $table = $setup->getConnection()
            ->newTable($setup->getTable('creatuity_cron'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'group_id',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'job_name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'status',
                TABLE::TYPE_TEXT,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                'pid',
                TABLE::TYPE_INTEGER,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                'return_code',
                TABLE::TYPE_INTEGER,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                'started_at',
                TABLE::TYPE_TIMESTAMP,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                'finished_at',
                TABLE::TYPE_TIMESTAMP,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                'error',
                TABLE::TYPE_TEXT,
                null,
                ['nullable' => true]
            );

        $setup->getConnection()->createTable($table);

    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    protected function upgrade_1_1_0(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->report()->printMessage('Adding cron table statistics data');

        $this->creatuity->dbConnection()->addColumn(
            $this->creatuity->dbConnection()->getTableName('creatuity_cron'),
            'stats_started',
            [
                'type' => Table::TYPE_INTEGER,
                'length' => 10,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Number of times this cron was started'
            ]
        );
        $this->creatuity->dbConnection()->addColumn(
            $this->creatuity->dbConnection()->getTableName('creatuity_cron'),
            'stats_finished_error',
            [
                'type' => Table::TYPE_INTEGER,
                'length' => 10,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Number of times this cron finished with error'
            ]
        );
        $this->creatuity->dbConnection()->addColumn(
            $this->creatuity->dbConnection()->getTableName('creatuity_cron'),
            'stats_finished_ok',
            [
                'type' => Table::TYPE_INTEGER,
                'length' => 10,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Number of times this cron finished successfully'
            ]
        );
        $this->creatuity->dbConnection()->addColumn(
            $this->creatuity->dbConnection()->getTableName('creatuity_cron'),
            'stats_last_duration',
            [
                'type' => Table::TYPE_FLOAT,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Number of seconds this cron was running'
            ]
        );
        $this->creatuity->dbConnection()->addColumn(
            $this->creatuity->dbConnection()->getTableName('creatuity_cron'),
            'stats_avg_duration_ok',
            [
                'type' => Table::TYPE_FLOAT,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Avarange duration of all successful runs'
            ]
        );
        $this->creatuity->dbConnection()->addColumn(
            $this->creatuity->dbConnection()->getTableName('creatuity_cron'),
            'stats_avg_duration_error',
            [
                'type' => Table::TYPE_FLOAT,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Avarange duration of all failed runs'
            ]
        );


    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    protected function upgrade_1_2_0(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->report()->printMessage("Adding settings fields to cron table");

        $this->creatuity->dbConnection()->addColumn(
            $this->creatuity->dbConnection()->getTableName('creatuity_cron'),
            'setting_enabled',
            [
                'type' => Table::TYPE_BOOLEAN,
                'length' => 10,
                'nullable' => false,
                'default' => true,
                'comment' => 'enabled',
                'after' => 'error'
            ]
        );

        $this->creatuity->dbConnection()->addColumn(
            $this->creatuity->dbConnection()->getTableName('creatuity_cron'),
            'setting_memorylimit',
            [
                'type' => Table::TYPE_INTEGER,
                'length' => 10,
                'nullable' => true,
                'default' => null,
                'comment' => 'memory limit',
                'after' => 'setting_enabled'
            ]
        );

        $this->creatuity->dbConnection()->addColumn(
            $this->creatuity->dbConnection()->getTableName('creatuity_cron'),
            'setting_timelimit',
            [
                'type' => Table::TYPE_INTEGER,
                'length' => 10,
                'nullable' => true,
                'default' => null,
                'comment' => 'time limit',
                'after' => 'setting_memorylimit'
            ]
        );

        $this->creatuity->dbConnection()->addColumn(
            $this->creatuity->dbConnection()->getTableName('creatuity_cron'),
            'schedule',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 10,
                'nullable' => false,
                'default' => '',
                'comment' => 'cron schedule from config (for debugging)'
            ]
        );

        $this->creatuity->dbConnection()->addColumn(
            $this->creatuity->dbConnection()->getTableName('creatuity_cron'),
            'output',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => null,
                'comment' => 'last bytes of command output',
                'after' => 'error'
            ]
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    protected function upgrade_1_2_1(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->report()->printMessage("Adding index");

        $this->creatuity->dbConnection()->addIndex(
            $this->creatuity->dbConnection()->getTableName('creatuity_cron'),
            'group_job',
            ['group_id', 'job_name'],
            AdapterInterface::INDEX_TYPE_UNIQUE
        );

        $this->creatuity->dbConnection()->addColumn(
            $this->creatuity->dbConnection()->getTableName('creatuity_cron'),
            'stats_avg_duration',
            [
                'type' => Table::TYPE_FLOAT,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Avarange duration of all runs'
            ]
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    protected function upgrade_1_2_2(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        $this->creatuity->dbConnection()->changeColumn(
            $this->creatuity->dbConnection()->getTableName('creatuity_cron'),
            'schedule',
            'schedule',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'default' => '',
                'comment' => 'cron schedule from config (for debugging)'
            ]
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    protected function upgrade_1_2_3(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->report()->printMessage("Adding memory usage stats");
        $this->creatuity->dbConnection()->addColumn(
            $this->creatuity->dbConnection()->getTableName('creatuity_cron'),
            'stats_last_memory',
            [
                'type' => Table::TYPE_FLOAT,
                'nullable' => false,
                'default' => 0,
                'comment' => 'last mem use'
            ]
        );

        $this->creatuity->dbConnection()->addColumn(
            $this->creatuity->dbConnection()->getTableName('creatuity_cron'),
            'stats_avg_memory',
            [
                'type' => Table::TYPE_FLOAT,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Avarange mam use'
            ]
        );

        $this->creatuity->dbConnection()->dropColumn(
            $this->creatuity->dbConnection()->getTableName('creatuity_cron'),
            'stats_avg_duration_ok'
        );
        $this->creatuity->dbConnection()->dropColumn(
            $this->creatuity->dbConnection()->getTableName('creatuity_cron'),
            'stats_avg_duration_error'
        );


    }

}