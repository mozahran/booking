<?php

declare(strict_types=1);

namespace App\Contract\Service;

use App\Domain\DataObject\Booking\Booking;
use App\Domain\Exception\TimeSlotNotAvailableException;

interface DoubleBookerBlockerInterface
{
    /**
     * @throws TimeSlotNotAvailableException
     */
    public function validate(
        Booking $booking,
    ): void;
}
