<?php
namespace Fsw\CronRunner\Plugin;

use Fsw\CronRunner\Console\RunAll;
use Fsw\CronRunner\Model\Cron\Scheduler;
use Magento\Framework\App\Cron;
use Magento\Framework\App\ResourceConnection;

class CronPlugin {

    /** @var RunAll */
    protected $runAll;

    public function __construct(RunAll $runAll) {
        $this->runAll = $runAll;
    }

    public function aroundLaunch(Cron $subject, callable $proceed) {
        $this->runAll->runAllJobs();
    }
}