<?php

namespace App\Contract\Service;

use App\Domain\DataObject\Booking\Booking;
use App\Domain\Exception\BookingNotFoundException;
use App\Domain\Exception\InvalidTimeRangeException;
use App\Domain\Exception\OccurrenceNotFoundException;
use App\Domain\Exception\SpaceNotFoundException;
use App\Domain\Exception\TimeSlotNotAvailableException;
use App\Domain\Exception\UserNotFoundException;
use App\Request\BookingRequest;

interface ChronoConductorInterface
{
    /**
     * @throws \Throwable
     * @throws TimeSlotNotAvailableException
     * @throws BookingNotFoundException
     * @throws InvalidTimeRangeException
     * @throws OccurrenceNotFoundException
     * @throws SpaceNotFoundException
     * @throws UserNotFoundException
     */
    public function createOrUpdate(
        BookingRequest $request,
        ?int $userId = null,
    ): Booking;
}
