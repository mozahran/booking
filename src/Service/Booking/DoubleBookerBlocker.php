<?php

namespace App\Service\Booking;

use App\Contract\Repository\OccurrenceRepositoryInterface;
use App\Contract\Service\DoubleBookerBlockerInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\Exception\TimeSlotNotAvailableException;

final readonly class DoubleBookerBlocker implements DoubleBookerBlockerInterface
{
    public function __construct(
        private OccurrenceRepositoryInterface $occurrenceRepository,
    ) {
    }

    public function validate(
        Booking $booking,
    ): void {
        $occurrenceSet = $booking->getOccurrences();
        if ($occurrenceSet->isEmpty()) {
            return;
        }

        $existingOccurrenceSet = $this->occurrenceRepository->findForConflictDetection(
            spaceId: $booking->getSpaceId(),
            occurrenceSet: $occurrenceSet,
            id: $booking->getId(),
        );

        if ($existingOccurrenceSet->isEmpty()) {
            return;
        }

        throw new TimeSlotNotAvailableException(occurrenceSet: $existingOccurrenceSet);
    }
}
