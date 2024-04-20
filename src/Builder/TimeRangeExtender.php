<?php

namespace App\Builder;

use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\Exception\AppException;
use App\Domain\Exception\InvalidTimeRangeException;

final class TimeRangeExtender
{
    private ?TimeRange $timeRange = null;
    private ?int $minutes = null;

    public function setTimeRange(
        TimeRange $timeRange,
    ): self {
        $this->timeRange = $timeRange;

        return $this;
    }

    public function setMinutes(
        int $minutes,
    ): self {
        $this->minutes = $minutes;

        return $this;
    }

    /**
     * @throws InvalidTimeRangeException
     * @throws AppException
     */
    public function build(): TimeRange
    {
        if (null === $this->minutes || null === $this->timeRange) {
            throw new AppException('You must set TimeRange and Minutes before calling ::build.');
        }

        $startsAt = \DateTime::createFromImmutable(object: $this->timeRange->getStartsAt())->modify(
            sprintf('-%d minutes', $this->minutes),
        );
        $endsAt = \DateTime::createFromImmutable(object: $this->timeRange->getEndsAt())->modify(
            sprintf('+%d minutes', $this->minutes),
        );

        return new TimeRange(
            startsAt: $startsAt->format(\DateTimeInterface::ATOM),
            endsAt: $endsAt->format(\DateTimeInterface::ATOM),
        );
    }
}
