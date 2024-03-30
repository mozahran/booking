<?php

namespace App\Contract\Service;

use App\Domain\DataObject\Set\OccurrenceSet;

interface JanitorInterface
{
    public function cleanObsoleteOccurrences(
        OccurrenceSet $existingOccurrenceSet,
        OccurrenceSet $occurrenceSet,
    ): void;
}
