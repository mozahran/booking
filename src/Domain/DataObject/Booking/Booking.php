<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Booking;

use App\Contract\DataObject\Identifiable;
use App\Contract\DataObject\Normalizable;
use App\Domain\DataObject\Set\OccurrenceSet;

final class Booking implements Identifiable, Normalizable
{
    public function __construct(
        private BookingSpec $bookingSpec,
        private OccurrenceSet $occurrences,
        private Status $status,
        private ?int $spaceId = null,
        private ?int $userId = null,
        private ?int $id = null,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getSpaceId(): ?int
    {
        return $this->spaceId;
    }

    public function getBookingSpec(): BookingSpec
    {
        return $this->bookingSpec;
    }

    public function getOccurrences(): OccurrenceSet
    {
        return $this->occurrences->sort();
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function isRepeatable(): bool
    {
        return $this->getOccurrences()->count() > 1;
    }

    public function isCancelled(): bool
    {
        return $this->getStatus()->isCancelled();
    }

    public function getTimeRange(): TimeRange
    {
        return $this->getBookingSpec()->getTimeRange();
    }

    public function normalize(): array
    {
        $status = $this->getStatus()->normalize();
        $bookingSpec = $this->getBookingSpec()->normalize();

        return [
            'id' => $this->getId(),
            'userId' => $this->getUserId(),
            'spaceId' => $this->getSpaceId(),
            ...$bookingSpec,
            'occurrences' => $this->getOccurrences()->normalize(),
            ...$status,
            'duration' => $this->getTimeRange()->getDuration(),
            'repeatable' => $this->isRepeatable(),
        ];
    }
}
