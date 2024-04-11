<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Booking;

use App\Contract\DataObject\Normalizable;

final class RecurrenceRule implements Normalizable
{
    /**
     * @param \DateTimeImmutable[] $excludedDates
     */
    public function __construct(
        private ?string $rule = null,
        private array $excludedDates = [],
    ) {
    }

    public function getRule(): ?string
    {
        return $this->rule;
    }

    /**
     * @return \DateTimeImmutable[]
     */
    public function getExcludedDates(): array
    {
        return $this->excludedDates;
    }

    public function normalize(): array
    {
        $normalizedExcludedDates = [];
        foreach ($this->getExcludedDates() as $excludedDate) {
            $normalizedExcludedDates[] = $excludedDate->format('Y-m-d');
        }

        return [
            'recurrenceRule' => $this->getRule(),
            'excludedDates' => $normalizedExcludedDates,
        ];
    }
}
