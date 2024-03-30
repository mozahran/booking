<?php

declare(strict_types=1);

namespace App\Contract\Resolver;

use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\DataObject\Set\BookingSet;
use App\Domain\Exception\BookingNotFoundException;

interface BookingResolverInterface
{
    /**
     * @throws BookingNotFoundException
     */
    public function resolve(
        int $id,
    ): Booking;

    /**
     * @param int[] $ids
     */
    public function resolveMany(
        array $ids,
    ): BookingSet;

    public function resolveRange(
        int $spaceId,
        TimeRange $timeRange,
    ): BookingSet;
}
