<?php

namespace App\Contract\Repository;

use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\DataObject\Set\BookingSet;
use App\Domain\DataObject\Set\TimeRangeSet;
use App\Domain\Exception\BookingNotFoundException;

interface BookingRepositoryInterface
{
    /**
     * @throws BookingNotFoundException
     */
    public function findOne(
        int $id,
    ): Booking;

    public function findMany(
        array $ids,
    ): BookingSet;

    public function findManyByRange(
        int $spaceId,
        TimeRange $timeRange,
    ): BookingSet;

    public function cancel(
        array $ids,
        int $cancellerId,
    ): void;

    public function countBufferConflicts(
        int $spaceId,
        TimeRangeSet $timeRangeSet,
    ): int;

    public function findByTimeRangeForUser(
        TimeRange $timeRange,
        int $userId,
    ): BookingSet;
}
