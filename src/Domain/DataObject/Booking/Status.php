<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Booking;

use App\Contract\DataObject\Normalizable;

final class Status implements Normalizable
{
    public function __construct(
        private bool $cancelled,
        private ?int $cancellerId = null,
    ) {
    }

    public function isCancelled(): bool
    {
        return $this->cancelled;
    }

    public function getCancellerId(): ?int
    {
        return $this->cancellerId;
    }

    public function normalize(): array
    {
        return [
            'cancelled' => $this->isCancelled(),
            'cancellerId' => $this->getCancellerId(),
        ];
    }
}
