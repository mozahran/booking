<?php

declare(strict_types=1);

namespace App\Contract\Persistor;

use App\Domain\DataObject\Booking\Booking;
use App\Domain\Exception\BookingNotFoundException;
use App\Domain\Exception\OccurrenceNotFoundException;
use App\Domain\Exception\SpaceNotFoundException;
use App\Domain\Exception\UserNotFoundException;

interface BookingPersistorInterface
{
    /**
     * @throws BookingNotFoundException
     * @throws SpaceNotFoundException
     * @throws UserNotFoundException
     * @throws OccurrenceNotFoundException
     */
    public function persist(
        Booking $booking,
    ): Booking;
}
