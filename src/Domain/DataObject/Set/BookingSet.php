<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Set;

use App\Domain\DataObject\Booking\Booking;

/**
 * @method Booking|null first()
 * @method Booking|null last()
 * @method Booking[]    items()
 * @method add(Booking $item)
 * @method remove(Booking $item)
 */
class BookingSet extends AbstractSet
{
    /**
     * @return int[]
     */
    public function spaceIds(): array
    {
        $result = [];
        $items = $this->items();
        foreach ($items as $item) {
            $spaceId = $item->getSpaceId();
            $result[$spaceId] = $spaceId;
        }

        return array_keys($result);
    }

    /**
     * @return int[]
     */
    public function ownerIds(): array
    {
        $result = [];
        $items = $this->items();
        foreach ($items as $item) {
            $userId = $item->getUserId();
            $result[$userId] = $userId;
        }

        return array_keys($result);
    }
}
