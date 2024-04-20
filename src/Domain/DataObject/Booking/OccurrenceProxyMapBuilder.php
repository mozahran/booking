<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Booking;

class OccurrenceProxyMapBuilder
{
    /**
     * @param array<string, Occurrence> $items
     */
    public function __construct(
        private array $items = [],
    ) {
    }

    public function add(
        Occurrence $occurrence,
    ): void {
        $dateTimeString = $occurrence->getTimeRange()->getDateTimeString();
        if (isset($this->items[$dateTimeString])) {
            return;
        }

        $this->items[$dateTimeString] = $occurrence;
    }

    public function build(): OccurrenceProxyMap
    {
        return new OccurrenceProxyMap(
            items: $this->items,
        );
    }
}
