<?php

namespace App\Contract\Service;

use App\Domain\DataObject\Booking\Booking;
use App\Domain\Exception\AppException;
use App\Domain\Exception\BookingNotFoundException;
use App\Domain\Exception\InvalidTimeRangeException;
use App\Domain\Exception\OccurrenceNotFoundException;
use App\Domain\Exception\RuleTypeMissingImplementationException;
use App\Domain\Exception\SpaceNotFoundException;
use App\Domain\Exception\TimeSlotNotAvailableException;
use App\Domain\Exception\UserNotFoundException;
use App\Request\BookingRequest;

interface ConductorInterface
{
    /**
     * @throws RuleTypeMissingImplementationException
     * @throws SpaceNotFoundException
     * @throws InvalidTimeRangeException
     * @throws UserNotFoundException
     * @throws BookingNotFoundException
     * @throws \Throwable
     * @throws OccurrenceNotFoundException
     * @throws TimeSlotNotAvailableException
     * @throws AppException
     */
    public function upsert(
        BookingRequest $bookingRequest,
        ?int $userId = null,
    ): Booking;
}
