<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Booking;

use App\Contract\DataObject\Normalizable;

final class BookingSpec implements Normalizable
{
    public function __construct(
        private TimeRange $timeRange,
        private ?RecurrenceRule $recurrenceRule = null,
    ) {
    }

    public function getTimeRange(): TimeRange
    {
        return $this->timeRange;
    }

    public function getRecurrenceRule(): ?RecurrenceRule
    {
        return $this->recurrenceRule;
    }

    public function normalize(): array
    {
        $normalizedOccurrenceRule = $this->getRecurrenceRule()?->normalize() ?? [];

        return [
            ...$this->getTimeRange()->normalize(),
            ...$normalizedOccurrenceRule,
        ];
    }
}
