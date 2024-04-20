<?php

namespace App\Tests\Unit\Utils;

use App\Builder\BookingBuilder;
use App\Builder\OccurrenceSetBuilder;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\Exception\InvalidTimeRangeException;
use DateTimeImmutable;
use DateTimeInterface;

class TestBookingFactory
{
    /**
     * @throws InvalidTimeRangeException
     */
    public static function createSingleOccurrenceBooking(
        DateTimeImmutable $startsAt,
        DateTimeImmutable $endsAt,
        int $spaceId = 1,
        int $userId = 1,
    ): Booking {
        $startsAt = $startsAt->format(DateTimeInterface::ATOM);
        $endsAt = $endsAt->format(DateTimeInterface::ATOM);
        $timeRange = new TimeRange(
            startsAt: $startsAt,
            endsAt: $endsAt,
        );
        $occurrenceSet = (new OccurrenceSetBuilder())
            ->add(startsAt: $startsAt, endsAt: $endsAt)
            ->build();

        return (new BookingBuilder())
            ->setOccurrenceSet($occurrenceSet)
            ->setTimeRange($timeRange)
            ->setSpaceId($spaceId)
            ->setUserId($userId)
            ->build();
    }
}