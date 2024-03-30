<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Booking;

use App\Contract\DataObject\Identifiable;
use App\Contract\DataObject\Normalizable;

final readonly class Occurrence implements Normalizable, Identifiable
{
    public function __construct(
        private TimeRange $timeRange,
        private Status $status,
        private ?int $bookingId = null,
        private ?int $id = null,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBookingId(): ?int
    {
        return $this->bookingId;
    }

    public function getTimeRange(): TimeRange
    {
        return $this->timeRange;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function isCancelled(): bool
    {
        return $this->getStatus()->isCancelled();
    }

    public function getCancellerId(): ?int
    {
        return $this->getStatus()?->getCancellerId();
    }

    public function normalize(): array
    {
        return [
            'id' => $this->getId(),
            'bookingId' => $this->getBookingId(),
            ...$this->getTimeRange()->normalize(),
            ...$this->getStatus()->normalize(),
        ];
    }
}
