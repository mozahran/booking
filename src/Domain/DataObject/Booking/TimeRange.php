<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Booking;

use App\Contract\DataObject\Normalizable;
use App\Contract\Request\TimeRangeAware;
use App\Domain\Exception\InvalidTimeRangeException;

final readonly class TimeRange implements Normalizable
{
    private \DateTimeImmutable $startsAt;
    private \DateTimeImmutable $endsAt;

    /**
     * @throws InvalidTimeRangeException
     */
    public function __construct(
        string $startsAt,
        string $endsAt,
    ) {
        try {
            if ('' === $startsAt || '' === $endsAt) {
                throw new InvalidTimeRangeException('Start & end time must be set!');
            }
            $this->startsAt = new \DateTimeImmutable($startsAt);
            $this->endsAt = new \DateTimeImmutable($endsAt);
            if ($this->endsAt < $this->startsAt) {
                throw new InvalidTimeRangeException('End time cannot be older than start time!');
            }
        } catch (\Exception) {
            throw new InvalidTimeRangeException('Invalid time range!');
        }
    }

    /**
     * @throws InvalidTimeRangeException
     */
    public static function fromRequest(
        TimeRangeAware $request,
    ): TimeRange {
        return new self(
            startsAt: $request->getStartsAt(),
            endsAt: $request->getEndsAt(),
        );
    }

    public function getStartsAt(): \DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function getStartTime(): string
    {
        return $this->getStartsAt()->format('H:i:00');
    }

    public function getEndsAt(): \DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function getEndTime(): string
    {
        return $this->getEndsAt()->format('H:i:00');
    }

    public function getDuration(): int
    {
        $startTimestamp = $this->getStartsAt()->getTimestamp();
        $endTimestamp = $this->getEndsAt()->getTimestamp();

        return intval(abs($startTimestamp - $endTimestamp) / 60);
    }

    public function isSame(
        string $dateString,
    ): bool {
        return $this->getDateString() === $dateString;
    }

    public function getDateString(): string
    {
        return $this->getStartsAt()->format('Y-m-d');
    }

    public function normalize(): array
    {
        return [
            'startsAt' => $this->getStartsAt()->format(\DateTimeInterface::ATOM),
            'endsAt' => $this->getEndsAt()->format(\DateTimeInterface::ATOM),
            'duration' => $this->getDuration(),
        ];
    }
}
