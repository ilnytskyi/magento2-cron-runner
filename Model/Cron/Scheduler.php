<?php

namespace Fsw\CronRunner\Model\Cron;

class Scheduler
{
    const MINUTE = 60;
    const HOUR = 3600;

    /**
     * @param $cronSchedule
     * @param $fromDate \DateTime
     * @param $toDate \DateTime
     * @return bool
     * @throws \Exception
     */
    public function shouldRunBetweenDates($cronSchedule, $fromDate, $toDate)
    {
        $parts = preg_split('/\s+/', $cronSchedule);

        if (count($parts) != 5) {
            throw new \Exception('unknown cron expression: ' . $cronSchedule);
        }

        $ret = clone $fromDate;
        $ret->modify('-' . $ret->format('s') . ' seconds +1 minute');
        if ($ret > $toDate) return false;


        $minute = (int)$ret->format('i');
        $addMinutes = $this->getNextValid($parts[0], $minute, 60) - $minute;
        $ret->modify('+' . ($addMinutes < 0 ? $addMinutes + 60 : $addMinutes) . ' minutes');
        if ($ret > $toDate) return false;

        $hour = (int)$ret->format('G');
        $addHours = $this->getNextValid($parts[1], $hour, 24) - $hour;
        $ret->modify('+' . ($addHours < 0 ? $addHours + 24 : $addHours) . ' hours');
        if ($ret > $toDate) return false;

        $safetyCount = 1024; //safety to ensure no ridiculous or impossible dates like 29-feb or monday 5th of may.
        while ($safetyCount > 0 && !(
            $this->matches($parts[2], $ret->format('j')) &&
            $this->matches($parts[3], $ret->format('n')) &&
            $this->matches($parts[4], $ret->format('w'))
        )) {
            $ret->modify('+1 day');
            if ($ret > $toDate) return false;
            $safetyCount --;
        }
        return $safetyCount == 0 ? false : $ret <= $toDate;
    }

    /**
     * @param $pattern
     * @param $from int
     * @param $max int
     * @return int
     * @throws \Exception
     */
    protected function getNextValid($pattern, $from, $max)
    {
        $best = $max + 1;
        foreach (explode(',', $pattern) as $part) {
            $step = $this->getNextValidFromPart($part, $from, $max) - $from;
            $best = min($step < 0 ? $step + $max : $step, $best);
        }
        return $from + $best;
    }

    protected function getNextValidFromPart($part, $from, $max)
    {
        if ($part == '*') {
            return $from;
        } elseif (strpos($part, '*/') === 0 && is_numeric($each = (int)substr($part, 2))) {
            return (ceil($from / $each) * $each) % $max;
        } elseif (strpos($part, '-') && count($range = explode('-', $part)) == 2) {
            if ($from < $range[0]) {
                return $range[0];
            } elseif ($from < $range[1]) {
                return $from;
            } else {
                return $range[0];
            }
        } elseif (is_numeric($part)) {
            return $part;
        } else {
            throw new \Exception('unknown cron expression part: ' . $part);
        }
    }

    protected function matches($pattern, $value)
    {
        foreach (explode(',', $pattern) as $part) {
            if ($part == '*') {
                return true;
            } elseif (strpos($part, '*/') === 0 && is_numeric($each = (int)substr($part, 2))) {
                if ($value % $each == 0) {
                    return true;
                }
            } elseif (strpos($part, '-') && count($range = explode('-', $part)) == 2) {
                if ($range[0] <= $value && $range[1] >= $value) {
                    return true;
                }
            } elseif (is_numeric($pattern)) {
                if ($pattern == $value) {
                    return true;
                }
            } else {
                throw new \Exception('unknown cron expression part: ' . $part);
            }
        }
        return false;
    }
}