<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Set;

use App\Domain\DataObject\Booking\Occurrence;
use App\Domain\DataObject\Booking\TimeRange;

/**
 * @method Occurrence|null first()
 * @method Occurrence|null last()
 * @method Occurrence[]    items()
 * @method add(Occurrence $item)
 * @method remove(Occurrence $item)
 */
class OccurrenceSet extends AbstractSet
{
    public function find(
        string $dateString,
    ): ?Occurrence {
        foreach ($this->items() as $occurrence) {
            if ($occurrence->getTimeRange()->isSame($dateString)) {
                return $occurrence;
            }
        }

        return null;
    }

    /**
     * @return int[]
     */
    public function bookingIds(): array
    {
        $result = [];
        $items = $this->items();
        foreach ($items as $item) {
            $bookingId = $item->getBookingId();
            $result[$bookingId] = $bookingId;
        }

        return array_keys($result);
    }

    public function sort(): self
    {
        usort(
            $this->items,
            fn (Occurrence $a, Occurrence $b) => $a->getTimeRange()->getStartsAt() <=> $b->getTimeRange()->getStartsAt(),
        );

        return $this;
    }

    /**
     * Aggregates the duration of all items in the set.
     */
    public function duration(): int
    {
        $result = 0;
        $items = $this->items();
        foreach ($items as $item) {
            $result += $item->getTimeRange()->getDuration();
        }

        return $result;
    }

    /**
     * @return string[] dates
     */
    public function dates(): array
    {
        $result = [];
        $items = $this->items();
        foreach ($items as $item) {
            $result[] = $item->getTimeRange()->getDateString();
        }

        return $result;
    }

    /**
     * @return TimeRange[]
     */
    public function timeRanges(): array
    {
        $result = [];
        $items = $this->items();
        foreach ($items as $item) {
            $result[] = $item->getTimeRange();
        }

        return $result;
    }
}
