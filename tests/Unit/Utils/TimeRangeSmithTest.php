<?php

declare(strict_types=1);

namespace App\Tests\Unit\Utils;

use App\Contract\Utils\TimeRangeSmithInterface;
use App\Domain\DataObject\Booking\TimeRange;
use App\Utils\TimeRangeSmith;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TimeRangeSmithTest extends KernelTestCase
{
    private TimeRangeSmithInterface $timeRangeSmith;
    protected function setUp(): void
    {
        parent::setUp();

        $this->timeRangeSmith = new TimeRangeSmith();
    }

    public function testExtendToDayRanges()
    {
        $given = new TimeRange(
            startsAt: '2025-01-01 15:00:00',
            endsAt: '2025-01-01 17:00:00',
        );

        $expected = new TimeRange(
            startsAt: '2025-01-01 00:00:00',
            endsAt: '2025-01-01 23:59:59.999999',
        );

        $actual = $this->timeRangeSmith->extendToDayRanges([$given]);

        $this->assertEquals($expected->getStartsAt(), $actual[0]->getStartsAt());
        $this->assertEquals($expected->getEndsAt(), $actual[0]->getEndsAt());
    }

    public function testExtendToWeekRanges()
    {
        $given = new TimeRange(
            startsAt: '2025-01-01 15:00:00',
            endsAt: '2025-01-01 17:00:00',
        );

        $expected = new TimeRange(
            startsAt: '2024-12-28 00:00:00',
            endsAt: '2025-01-03 23:59:59.999999',
        );

        $actual = $this->timeRangeSmith->extendToWeekRanges([$given]);

        $this->assertEquals($expected->getStartsAt(), $actual[0]->getStartsAt());
        $this->assertEquals($expected->getEndsAt(), $actual[0]->getEndsAt());
    }

    public function testExtendToMonthRanges()
    {
        $given = new TimeRange(
            startsAt: '2025-01-01 15:00:00',
            endsAt: '2025-01-01 17:00:00',
        );

        $expected = new TimeRange(
            startsAt: '2025-01-01 00:00:00',
            endsAt: '2025-01-31 23:59:59.999999',
        );

        $actual = $this->timeRangeSmith->extendToMonthRanges([$given]);

        $this->assertEquals($expected->getStartsAt(), $actual[0]->getStartsAt());
        $this->assertEquals($expected->getEndsAt(), $actual[0]->getEndsAt());
    }
}
