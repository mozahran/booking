<?php

namespace App\Service;

use App\Contract\Repository\OccurrenceRepositoryInterface;
use App\Contract\Service\JanitorInterface;
use App\Domain\DataObject\Set\OccurrenceSet;

final class Janitor implements JanitorInterface
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

        $obsoleteOccurrences = new OccurrenceSet();
        $existingOccurrences = $existingOccurrenceSet->items();
        foreach ($existingOccurrences as $existingOccurrence) {
            if (!$occurrenceSet->find(dateString: $existingOccurrence->getTimeRange()->getDateString())) {
                $obsoleteOccurrences->add(item: $existingOccurrence);
            }
        }

        $this->occurrenceRepository->delete(ids: $obsoleteOccurrences->ids());
    }
}
