<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Booking;

use App\Contract\DataObject\Normalizable;
use App\Contract\Request\TimeRangeAware;
use App\Domain\Exception\InvalidTimeRangeException;
use DateTimeImmutable;
use Exception;

final readonly class TimeRange implements Normalizable
{
    public const SHORT_FORMAT = 'Y-m-d H:i';
    public const DATE_FORMAT = 'Y-m-d';
    const SHORT_TIME_FORMAT = 'H:i';

    private DateTimeImmutable $startsAt;
    private DateTimeImmutable $endsAt;

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
            $this->startsAt = new DateTimeImmutable($startsAt);
            $this->endsAt = new DateTimeImmutable($endsAt);
            if ($this->endsAt < $this->startsAt) {
                throw new InvalidTimeRangeException('End time cannot be older than start time!');
            }
        } catch (Exception $exception) {
            throw new InvalidTimeRangeException($exception->getMessage());
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

    public function getStartsAt(): DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function getStartMinutes(): int
    {
        $startsAt = $this->getStartsAt();

        $h = (int) $startsAt->format('G');
        $m = (int) $startsAt->format('i');

        return ($h * 60) + $m;
    }

    public function getStartTime(): string
    {
        return $this->getStartsAt()->format('H:i:00');
    }

    public function getEndsAt(): DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function getEndMinutes(): int
    {
        $endsAt = $this->getEndsAt();

        $h = (int) $endsAt->format('G');
        $m = (int) $endsAt->format('i');

        return ($h * 60) + $m;
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
        return $this->getStartsAt()->format(self::DATE_FORMAT);
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
