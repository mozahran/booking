<?php

namespace App\Utils;

use App\Contract\Utils\TimeRangeSmithInterface;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\Exception\InvalidTimeRangeException;

class TimeRangeSmith implements TimeRangeSmithInterface
{
    /**
     * @param TimeRange[] $timeRanges
     *
     * @return TimeRange[]
     *
     * @throws InvalidTimeRangeException
     */
    public function extendToDayRanges(array $timeRanges): array
    {
        $result = [];
        foreach ($timeRanges as $timeRange) {
            $startsAtFormatted = $timeRange->getStartOfDay()->format(TimeRange::DATE_TIME_FORMAT_MICROSECONDS);
            $endsAtFormatted = $timeRange->getEndOfDay()->format(TimeRange::DATE_TIME_FORMAT_MICROSECONDS);
            $key = $startsAtFormatted.$endsAtFormatted;
            $result[$key] = new TimeRange(
                startsAt: $startsAtFormatted,
                endsAt: $endsAtFormatted,
            );
        }

        return array_values($result);
    }

    /**
     * @return TimeRange[]
     *
     * @throws InvalidTimeRangeException
     */
    public function extendToWeekRanges(array $timeRanges): array
    {
        $result = [];
        foreach ($timeRanges as $timeRange) {
            $startsAtFormatted = $timeRange->getStartOfWeek()->format(TimeRange::DATE_TIME_FORMAT_MICROSECONDS);
            $endsAtFormatted = $timeRange->getEndOfWeek()->format(TimeRange::DATE_TIME_FORMAT_MICROSECONDS);
            $key = $startsAtFormatted.$endsAtFormatted;
            $result[$key] = new TimeRange(
                startsAt: $startsAtFormatted,
                endsAt: $endsAtFormatted,
            );
        }

        return array_values($result);
    }

    /**
     * @return TimeRange[]
     *
     * @throws InvalidTimeRangeException
     */
    public function extendToMonthRanges(array $timeRanges): array
    {
        $result = [];
        foreach ($timeRanges as $timeRange) {
            $startsAtFormatted = $timeRange->getStartOfMonth()->format(TimeRange::DATE_TIME_FORMAT_MICROSECONDS);
            $endsAtFormatted = $timeRange->getEndOfMonth()->format(TimeRange::DATE_TIME_FORMAT_MICROSECONDS);
            $key = $startsAtFormatted.$endsAtFormatted;
            $result[$key] = new TimeRange(
                startsAt: $startsAtFormatted,
                endsAt: $endsAtFormatted,
            );
        }

        return array_values($result);
    }
}
