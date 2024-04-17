<?php

namespace App\Contract\Service\Booking;

use App\Domain\DataObject\Booking\Booking;
use App\Domain\Exception\AppException;
use App\Domain\Exception\BookingNotFoundException;
use App\Domain\Exception\InvalidTimeRangeException;
use App\Domain\Exception\OccurrenceNotFoundException;
use App\Domain\Exception\SpaceNotFoundException;
use App\Domain\Exception\TimeSlotNotAvailableException;
use App\Domain\Exception\UserNotFoundException;
use App\Request\BookingRequest;
use Throwable;

interface ConductorInterface
{
    /**
     * @throws SpaceNotFoundException
     * @throws InvalidTimeRangeException
     * @throws UserNotFoundException
     * @throws BookingNotFoundException
     * @throws OccurrenceNotFoundException
     * @throws TimeSlotNotAvailableException
     * @throws Throwable
     * @throws AppException
     */
    public function upsert(
        BookingRequest $bookingRequest,
        ?int $userId = null,
    ): Booking;
}
