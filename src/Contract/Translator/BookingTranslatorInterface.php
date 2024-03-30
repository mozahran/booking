<?php

declare(strict_types=1);

namespace App\Contract\Translator;

use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Booking\Occurrence;
use App\Domain\DataObject\Set\BookingSet;
use App\Domain\DataObject\Set\OccurrenceSet;
use App\Domain\Exception\BookingNotFoundException;
use App\Domain\Exception\SpaceNotFoundException;
use App\Domain\Exception\UserNotFoundException;
use App\Entity\BookingEntity;
use App\Entity\OccurrenceEntity;

interface BookingTranslatorInterface
{
    public function toBooking(
        BookingEntity $entity,
    ): Booking;

    public function toBookingSet(
        array $entities,
    ): BookingSet;

    public function toOccurrence(
        OccurrenceEntity $entity,
    ): Occurrence;

    public function toOccurrenceSet(
        array $entities,
    ): OccurrenceSet;

    /**
     * @throws SpaceNotFoundException
     * @throws BookingNotFoundException
     * @throws UserNotFoundException
     */
    public function toBookingEntity(
        Booking $booking,
    ): BookingEntity;
}
