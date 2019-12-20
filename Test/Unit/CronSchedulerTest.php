<?php

namespace Fsw\CronRunner\Test\Unit;

use Fsw\CronRunner\Model\Cron\Scheduler;

class CronSchedulerTest extends \PHPUnit\Framework\TestCase
{
    /** @var Scheduler */
    private $scheduler;

    protected function setUp()
    {
        $this->scheduler = new Scheduler();
    }

    /**
     * @param string $expression
     * @param string $startDate
     * @param string $endDate
     * @param bool $expectedResult
     * @dataProvider runsBetweenDataProvider
     */
    public function testRunsBetween($expression, $startDate, $endDate, $expectedResult)
    {
        $comment = "$expression " . ($expectedResult ? 'is' : 'is not') . " between $startDate and $endDate";
        $start = new \DateTime($startDate);
        $finish = new \DateTime($endDate);

        $this->assertEquals($expectedResult, $this->scheduler->shouldRunBetweenDates($expression, $start, $finish), $comment);
    }

    /**
     * @param string $expression
     * @param string $startDate
     * @param string $endDate
     * @param int $expectedResult
     * @dataProvider countRunsBetweenDataProvider
     */
    public function testCountRunsBetween($expression, $startDate, $endDate, $expectedResult)
    {
        $count = 0;
        $start = new \DateTime($startDate);
        $finish = new \DateTime($endDate);

        $next = clone $start;

        while ($start < $finish) {
            $next->modify('+1 minutes');
            if ($this->scheduler->shouldRunBetweenDates($expression, $start, $next)) {
                //echo $start->format('Y-m-d H:i:s') . ' => ' . $next->format('Y-m-d H:i:s') . 'OK' . "\n";
                $count ++;
            }
            $start->modify('+1 minutes');
        }

        $this->assertEquals($expectedResult, $count, 'was run '. $expectedResult . ' times');
    }

    /**
     * @return array
     */
    public function runsBetweenDataProvider()
    {
        return [
            ['* * * * *', '2018-10-12 12:00:10', '2018-10-12 12:00:15', false],
            ['* * * * *', '2018-10-12 12:00:10', '2018-10-12 12:01:15', true],

            ['10 * * * *', '2018-10-12 12:00:10', '2018-10-12 12:01:15', false],
            ['10 * * * *', '2018-10-12 12:00:10', '2018-10-12 12:10:15', true],
            ['10 * * * *', '2018-10-12 12:10:10', '2018-10-12 12:11:15', false],

            ['0 * * * *', '2018-10-12 12:10:10', '2018-10-12 12:11:15', false],
            ['0 * * * *', '2018-10-12 12:10:10', '2018-10-12 13:00:01', true],
            ['0 * * * *', '2018-10-12 13:00:10', '2018-10-12 13:59:59', false],

            ['0 12 * * *', '2018-10-12 12:10:10', '2018-10-12 13:00:01', false],
            ['0 12 * * *', '2018-10-12 11:10:10', '2018-10-12 12:01:01', true],

            ['0,10,20,30,40,50 12 * * *', '2018-10-14 12:39:10', '2018-10-14 12:40:10', true],
            ['0 12 */2 * *', '2018-10-12 12:00:01', '2018-10-14 11:59:59', false],
            ['0 12 */2 * *', '2018-10-12 12:00:01', '2018-10-14 12:00:00', true],

            ['* * 30 4 4', '2018-01-01 00:00:00', '2020-01-01 00:00:00', false],
            ['* * 30 4 4', '2018-01-01 00:00:00', '2021-01-01 00:00:00', true]
        ];
    }

    /**
     * @return array
     */
    public function  countRunsBetweenDataProvider()
    {
        return [
            ['0,10,20,30,40,50 12 * * *','2018-10-10 13:10:10','2018-10-14 12:41:01', 23],
            ['*/10 12 * * *','2018-10-10 13:10:10','2018-10-14 12:41:01', 23],
            ['* */3 */2 */2 *','2018-01-15 00:00:00','2018-2-02 23:59:59', 480],
            ['7 */2 * * *','2018-01-01 00:00:00','2018-01-01 12:00:00', 6]
        ];
    }
}
