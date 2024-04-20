<?php

namespace App\Tests\Unit\Builder;

use App\Builder\TimeRangeExtender;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\Exception\AppException;
use Generator;
use PHPUnit\Framework\TestCase;

class TimeRangeExtenderTest extends TestCase
{
    /**
     * @dataProvider dataProviderForTestBuild
     */
    public function testBuild(
        bool $success,
        array $params,
    ): void {
        $timeRange = (new TimeRangeExtender())
            ->setMinutes($params['minutes'])
            ->setTimeRange($params['timeRange'])
            ->build();

        if (true === $success) {
            $this->assertEquals($params['expected'], $timeRange);
        } else {
            $this->assertNotEquals($params['expected'], $timeRange);
        }
    }

    public function testBuildWithoutSpecifyingMinutes()
    {
        $this->expectException(AppException::class);

        (new TimeRangeExtender())
            ->setTimeRange(new TimeRange('2000-01-01 15:00', '2000-01-01 16:00'))
            ->build();
    }

    public function testBuildWithoutSpecifyingTimeRange()
    {
        $this->expectException(AppException::class);

        (new TimeRangeExtender())
            ->setMinutes(60)
            ->build();
    }

    public static function dataProviderForTestBuild(): Generator
    {
        yield 'Case #1' => [
            'success' => true,
            'params' => [
                'minutes' => 60,
                'timeRange' => new TimeRange('2000-01-01 15:00', '2000-01-01 16:00'),
                'expected' => new TimeRange('2000-01-01 14:00', '2000-01-01 17:00'),
            ],
        ];

        yield 'Case #2' => [
            'success' => false,
            'params' => [
                'minutes' => 60,
                'timeRange' => new TimeRange('2000-01-01 15:00', '2000-01-01 16:00'),
                'expected' => new TimeRange('2000-01-01 15:00', '2000-01-01 16:00'),
            ],
        ];
    }
}
