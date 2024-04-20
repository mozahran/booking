<?php

declare(strict_types=1);

namespace App\Contract\Utils;

use App\Domain\DataObject\Booking\TimeRange;

interface TimeRangeSmithInterface
{
    /**
     * @param TimeRange[] $timeRanges
     *
     * @return TimeRange[]
     */
    public function extendToDayRanges(
        array $timeRanges,
    ): array;

    /**
     * @param TimeRange[] $timeRanges
     *
     * @return TimeRange[]
     */
    public function extendToWeekRanges(
        array $timeRanges,
    ): array;

    /**
     * @param TimeRange[] $timeRanges
     *
     * @return TimeRange[]
     */
    public function extendToMonthRanges(
        array $timeRanges,
    ): array;
}
