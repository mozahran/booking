<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Set;

use App\Domain\DataObject\Booking\Occurrence;

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

    public function sort()
    {
        usort(
            $this->items,
            fn (Occurrence $a, Occurrence $b) => $a->getTimeRange()->getStartsAt() <=> $b->getTimeRange()->getStartsAt(),
        );

        return $this;
    }
}
