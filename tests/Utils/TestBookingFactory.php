<?php

namespace App\Tests\Utils;

use App\Builder\BookingBuilder;
use App\Builder\OccurrenceSetBuilder;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\Exception\InvalidTimeRangeException;

class TestBookingFactory
{
    /**
     * @throws InvalidTimeRangeException
     */
    public static function createSingleOccurrenceBooking(
        string $startsAt,
        string $endsAt,
        int $spaceId,
    ): Booking {
        $timeRange = new TimeRange(
            startsAt: $startsAt,
            endsAt: $endsAt,
        );
        $occurrenceSet = (new OccurrenceSetBuilder())
            ->setTimeRange($timeRange)
            ->add(startsAt: $startsAt, endsAt: $endsAt)
            ->build();

        return (new BookingBuilder())
            ->setOccurrenceSet($occurrenceSet)
            ->setTimeRange($timeRange)
            ->setSpaceId($spaceId)
            ->build();
    }
}