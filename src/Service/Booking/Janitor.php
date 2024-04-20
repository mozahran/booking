<?php

namespace App\Service\Booking;

use App\Contract\Repository\OccurrenceRepositoryInterface;
use App\Contract\Service\JanitorInterface;
use App\Domain\DataObject\Set\OccurrenceSet;

final readonly class Janitor implements JanitorInterface
{
    public function __construct(
        private OccurrenceRepositoryInterface $occurrenceRepository,
    ) {
    }

    public function cleanObsoleteOccurrences(
        OccurrenceSet $existingOccurrenceSet,
        OccurrenceSet $occurrenceSet,
    ): void {
        if ($existingOccurrenceSet->isEmpty()) {
            return;
        }

        $obsoleteOccurrenceSet = new OccurrenceSet();
        $existingOccurrences = $existingOccurrenceSet->items();
        foreach ($existingOccurrences as $existingOccurrence) {
            $matchingOccurrence = $occurrenceSet->find(
                dateString: $existingOccurrence->getTimeRange()->getDateString(),
            );
            if (null === $matchingOccurrence) {
                $obsoleteOccurrenceSet->add(item: $existingOccurrence);
            }
        }

        $this->occurrenceRepository->delete(
            ids: $obsoleteOccurrenceSet->ids(),
        );
    }
}
