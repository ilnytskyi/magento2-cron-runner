<?php

namespace Creatuity\CronRunner\Model;

use Creatuity\CronRunner\Model\Cron\Scheduler;
use DateTime;
use Magento\Framework\App\ResourceConnection;
use Zend\Db\Sql\Expression;

class CronJob
{
    public $groupId;
    public $jobName;
    public $schedule;
    public $instance;
    public $method;
    public $priority;

    const STATUS_IDLE = 'idle';
    const STATUS_RUNNING = 'running';
    const STATUS_ERROR = 'error';

    /** @var ResourceConnection */
    private $resourceConnection;

    /** @var Scheduler */
    private $scheduler;

    public function __construct(
		Scheduler $scheduler,
		ResourceConnection $resourceConnection, $groupId, $jobName, $schedule, $instance, $method, $priority) {
        $this->groupId = $groupId;
        $this->jobName = $jobName;
        $this->schedule = $schedule;
        $this->instance = $instance;
        $this->method = $method;
        $this->priority = $priority;
    	$this->resourceConnection = $resourceConnection;
		$this->scheduler = $scheduler;
	}

    /**
     *
     */
    public function isValid()
    {
        if (empty($this->schedule)) {
            $this->setError('missing schedule');
            return false;
        }
        if (empty($this->instance)) {
            $this->setError('missing isntance');
            return false;
        }
        if (empty($this->method)) {
            $this->setError('missing method');
            return false;
        }
        /*if (!class_exists($this->instance)) {
            $this->setError('class ' . $this->instance . ' does not exist');
            return false;
        }
        if (!method_exists($this->instance, $this->method)) {
            $this->setError('instance method ' . $this->method . '  does not exist in class ' . $this->instance);
            return false;
        }*/
        return true;
    }

    /**
     * @param $field
     * @return string
     */
    private function getRowData($field)
    {
        if (!isset($this->row)) {
            $connection = $this->resourceConnection->getConnection();
            $this->row = $connection->fetchRow($connection->select()
                ->from('creatuity_cron')
                ->where('group_id = ?', $this->groupId)
                ->where('job_name = ?', $this->jobName));
            $this->resourceConnection->closeConnection();
        }
        return empty($this->row[$field]) ? null : $this->row[$field];
    }

    /**
     * @return DateTime|null
     */
    public function getLastStartTime()
    {
        $status = $this->getRowData('status');

        if ($status === null) {
            return null;
        } else if ($status == self::STATUS_RUNNING) {
            $pid = $this->getRowData('pid');
            if (file_exists( '/proc/' . $pid )) {
                return new DateTime();
            } else {
                //marking previous job as finished
                $this->setError('Seems that PID ' . $pid . ' has been killed.');
                $this->markAsFinished($this->getRowData('pid'), 1, 0, 0);
                return new DateTime($this->getRowData('started_at'));
            }
        } else {
            return new DateTime($this->getRowData('started_at'));
        }
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getRowData('setting_enabled') !== '0';
    }

    /**
     * @return int
     */
    public function getTimeLimit()
    {
        return (int)$this->getRowData('setting_timelimit');
    }

    /**
     * @return int
     */
    public function getMemoryLimit()
    {
        return (int)$this->getRowData('setting_memorylimit');
    }

    /**
     * @param $pid
     */
    public function markAsStarted($pid)
    {
        $this->resourceConnection->getConnection()->insertOnDuplicate('creatuity_cron', [
            'group_id' => $this->groupId,
            'job_name' => $this->jobName,
            'schedule' => $this->schedule,
            'pid' => $pid,
            'status' => self::STATUS_RUNNING,
            'started_at' => (new DateTime())->format("Y-m-d H:i:s"),
            'finished_at' => null,
            'error' => null,
            'output' => null,
            'stats_started' => 1
        ], [
            'schedule' => new Expression('"' .$this->schedule . '"'),
            'pid' => $pid,
            'status' => new Expression('"' . self::STATUS_RUNNING . '"'),
            'started_at' => new Expression('"' . (new DateTime())->format("Y-m-d H:i:s") . '"'),
            'finished_at' => null,
            'error' => null,
            'output' => null,
            'stats_started' => new Expression('stats_started + 1')
        ]);
        $this->resourceConnection->closeConnection();
    }

    /**
     * @param $pid
     * @param $return_code
     */
    public function markAsFinished($pid, $return_code, $time_ms, $mem_kb)
    {
        $ok = ($return_code == 0);
        $time_ms = (int)$time_ms;
        $mem_kb = (int)$mem_kb;

        $now = (new DateTime())->format("Y-m-d H:i:s");
        $this->resourceConnection->getConnection()->update('creatuity_cron', [
            'status' => $ok ? self::STATUS_IDLE : self::STATUS_ERROR,
            'return_code' => $return_code,
            'finished_at' => $now,
            'stats_finished_error' => new \Zend_Db_Expr($ok ? 'stats_finished_error' : 'stats_finished_error + 1'),
            'stats_finished_ok' => new \Zend_Db_Expr($ok ? 'stats_finished_ok + 1' : 'stats_finished_ok'),
            'stats_last_duration' => new \Zend_Db_Expr($time_ms),
            'stats_avg_duration' => new \Zend_Db_Expr( "(stats_avg_duration * (stats_started - 1) + $time_ms) / (stats_started) "),
            'stats_last_memory' => new \Zend_Db_Expr($mem_kb),
            'stats_avg_memory' => new \Zend_Db_Expr( "(stats_avg_memory * (stats_started - 1) + $mem_kb) / (stats_started) "),
        ], [
            'group_id = ?' => $this->groupId,
            'job_name = ?' => $this->jobName,
            'pid = ?' => $pid
        ]);
        $this->resourceConnection->closeConnection();
    }



    /**
     * @param $error
     */
    public function setError($error)
    {
        $this->resourceConnection->getConnection()->insertOnDuplicate('creatuity_cron', [
            'group_id' => $this->groupId,
            'job_name' => $this->jobName,
            'status' => self::STATUS_ERROR,
            'error' => $error
        ]);
        $this->resourceConnection->closeConnection();
    }

    /**
     * @param $output
     */
    public function setOutput($output)
    {
        $this->resourceConnection->getConnection()->insertOnDuplicate('creatuity_cron', [
            'group_id' => $this->groupId,
            'job_name' => $this->jobName,
            'output' => $output
        ]);
        $this->resourceConnection->closeConnection();
    }

    /**
     * @param DateTime $at
     * @return bool
     * @throws \Exception
     */
    public function shouldBeExecuted(DateTime $at)
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $lastStart = $this->getLastStartTime();
        if ($lastStart === null) {
            //cron was never run
            return true;
        }

        try {
            return $this->scheduler->shouldRunBetweenDates($this->schedule, $lastStart, $at);
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

}
