<?php

require_once '../Model/Cron/Scheduler.php';


$scheduler =  new \Creatuity\CronRunner\Model\Cron\Scheduler();


function testRunBetween($expr, $from, $to, $expected) {
    global $scheduler;
    $comment = "$expr " . ($expected ? 'is' : 'is not') . " between $from and $to";
    assert($scheduler->shouldRunBetweenDates($expr, new \DateTime($from), new \DateTime($to)) === $expected, $comment);
}

function testCountRuns($expr, $from, $to, $expected) {
    global $scheduler;
    $count = 0;
    $start = new \DateTime($from);
    $finish = new \DateTime($to);

    $next = clone $start;

    while ($start < $finish) {
        $next->modify('+1 minutes');
        if ($scheduler->shouldRunBetweenDates($expr, $start, $next)) {
            //echo $start->format('Y-m-d H:i:s') . ' => ' . $next->format('Y-m-d H:i:s') . 'OK' . "\n";
            $count ++;
        }
        $start->modify('+1 minutes');
    }
    assert($count == $expected, 'was run '. $expected . ' times (not ' . $count . ')');
}



testRunBetween('* * * * *','2018-10-12 12:00:10','2018-10-12 12:00:15', false);
testRunBetween('* * * * *','2018-10-12 12:00:10','2018-10-12 12:01:15', true);

testRunBetween('10 * * * *','2018-10-12 12:00:10','2018-10-12 12:01:15', false);
testRunBetween('10 * * * *','2018-10-12 12:00:10','2018-10-12 12:10:15', true);
testRunBetween('10 * * * *','2018-10-12 12:10:10','2018-10-12 12:11:15', false);

testRunBetween('0 * * * *','2018-10-12 12:10:10','2018-10-12 12:11:15', false);
testRunBetween('0 * * * *','2018-10-12 12:10:10','2018-10-12 13:00:01', true);
testRunBetween('0 * * * *','2018-10-12 13:00:10','2018-10-12 13:59:59', false);

testRunBetween('0 12 * * *','2018-10-12 12:10:10','2018-10-12 13:00:01', false);
testRunBetween('0 12 * * *','2018-10-12 11:10:10','2018-10-12 12:01:01', true);


testRunBetween('0,10,20,30,40,50 12 * * *','2018-10-14 12:39:10','2018-10-14 12:40:10', true);

testRunBetween('0 12 */2 * *','2018-10-12 12:00:01','2018-10-14 11:59:59', false);
testRunBetween('0 12 */2 * *','2018-10-12 12:00:01','2018-10-14 12:00:00', true);

testRunBetween('* * 30 4 4','2018-01-01 00:00:00','2020-01-01 00:00:00', false);
testRunBetween('* * 30 4 4','2018-01-01 00:00:00','2021-01-01 00:00:00', true);

testCountRuns('0,10,20,30,40,50 12 * * *','2018-10-10 13:10:10','2018-10-14 12:41:01', 23);
testCountRuns('*/10 12 * * *','2018-10-10 13:10:10','2018-10-14 12:41:01', 23);
testCountRuns('7 */3 */2 */2 *','2018-01-01 00:00:00','2018-12-31 23:59:59', 712);
testCountRuns('7 */2 * * *','2018-01-01 00:00:00','2018-01-01 12:00:00', 6);