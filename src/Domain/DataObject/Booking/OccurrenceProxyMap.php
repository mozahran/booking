<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Booking;

final readonly class OccurrenceProxyMap
{
    /**
     * @param array<string, Occurrence> $items
     */
    public function __construct(
        private array $items = [],
    ) {
    }

    public static function empty(): self
    {
        return new self();
    }

    public function findFor(
        string $dateTimeString,
    ): ?Occurrence {
        return $this->items[$dateTimeString] ?? null;
    }

    /**
     * @return array<string, Occurrence>
     */
    public function items(): array
    {
        return $this->items;
    }
}
