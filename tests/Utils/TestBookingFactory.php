<?php

namespace App\Tests\Utils;

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
        int $spaceId,
        int $userId,
    ): Booking {
        $startsAt = $startsAt->format(DateTimeInterface::ATOM);
        $endsAt = $endsAt->format(DateTimeInterface::ATOM);
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
            ->setUserId($userId)
            ->build();
    }
}