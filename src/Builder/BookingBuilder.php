<?php

declare(strict_types=1);

namespace App\Builder;

use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Booking\BookingSpec;
use App\Domain\DataObject\Booking\RecurrenceRule;
use App\Domain\DataObject\Booking\Status;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\DataObject\Set\OccurrenceSet;
use App\Domain\Exception\InvalidTimeRangeException;

final class BookingBuilder
{
    private TimeRange $timeRange;
    private ?RecurrenceRule $recurrenceRule = null;
    private Status $status;
    private ?int $id = null;
    private ?int $userId = null;
    private ?int $spaceId = null;
    private OccurrenceSet $occurrenceSet;

    public function __construct()
    {
        $this->occurrenceSet = new OccurrenceSet();
        $this->status = new Status(
            cancelled: false,
            cancellerId: null,
        );
    }

    public function setTimeRange(
        TimeRange $timeRange,
    ): self {
        $this->timeRange = $timeRange;

        return $this;
    }

    public function setRecurrenceRule(
        RecurrenceRule $recurrenceRule,
    ): self {
        $this->recurrenceRule = $recurrenceRule;

        return $this;
    }

    public function setOccurrenceSet(
        OccurrenceSet $occurrenceSet,
    ): self {
        $this->occurrenceSet = $occurrenceSet;

        return $this;
    }

    public function setCancelled(
        int $cancellerId,
    ): self {
        $this->status = new Status(
            cancelled: true,
            cancellerId: $cancellerId,
        );

        return $this;
    }

    public function setId(
        ?int $id,
    ): self {
        $this->id = $id;

        return $this;
    }

    public function setUserId(
        int $id,
    ): self {
        $this->userId = $id;

        return $this;
    }

    public function setSpaceId(
        int $id,
    ): self {
        $this->spaceId = $id;

        return $this;
    }

    /**
     * @throws InvalidTimeRangeException
     */
    public function build(): Booking
    {
        if (!isset($this->timeRange)) {
            throw new InvalidTimeRangeException('Time range must be set before building the booking!');
        }

        $bookingSpec = new BookingSpec(
            timeRange: $this->timeRange,
            recurrenceRule: $this->recurrenceRule,
        );

        return new Booking(
            bookingSpec: $bookingSpec,
            occurrences: $this->occurrenceSet,
            status: $this->status,
            spaceId: $this->spaceId,
            userId: $this->userId,
            id: $this->id,
        );
    }
}
