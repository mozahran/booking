<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Booking;

use App\Contract\DataObject\Normalizable;
use App\Domain\Enum\Rule\Predicate;
use App\Domain\Exception\InvalidTimeRangeException;

final readonly class TimeRange implements Normalizable
{
    public const DATETIME_FORMAT = 'Y-m-d H:i';
    public const DATE_FORMAT = 'Y-m-d';
    public const DATE_TIME_FORMAT_MICROSECONDS = 'Y-m-d H:i:s.u';

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
        } catch (\Exception $exception) {
            throw new InvalidTimeRangeException($exception->getMessage());
        }
    }

    /**
     * @throws InvalidTimeRangeException
     */
    public static function withDefault(
        ?string $startsAt = null,
        ?string $endsAt = null,
    ): self {
        $now = new \DateTimeImmutable(datetime: 'now');

        $startsAt ??= $now->format('Y-m-d 00:00:00');
        $endsAt ??= $now->format('Y-m-d 23:59:59');

        return new self(
            startsAt: $startsAt,
            endsAt: $endsAt,
        );
    }

    public function getStartsAt(): \DateTimeImmutable
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

    public function getMinutesToMidnight(): int
    {
        return abs(Predicate::MINUTES_IN_DAY - $this->getStartMinutes());
    }

    public function getMinutesFromMidnight(): int
    {
        return $this->getStartMinutes();
    }

    public function getStartTime(): string
    {
        return $this->getStartsAt()->format('H:i:00');
    }

    public function getDateTimeString(): string
    {
        return $this->getStartsAt()->format(self::DATETIME_FORMAT);
    }

    public function getEndsAt(): \DateTimeImmutable
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

    public function getWeekdayNumber(): int
    {
        return (int) $this->getStartsAt()->format('w');
    }

    public function getStartOfDay(): \DateTimeImmutable
    {
        $datetime = $this->getStartsAt()->format('Y-m-d 00:00:00');

        return new \DateTimeImmutable($datetime);
    }

    public function getEndOfDay(): \DateTimeImmutable
    {
        $datetime = $this->getEndsAt()->format('Y-m-d 23:59:59.999999');

        return new \DateTimeImmutable($datetime);
    }

    public function getStartOfWeek(int $weekStartsAt = 6): \DateTimeImmutable
    {
        $daysPerWeek = 7;
        $startsAt = \DateTime::createFromImmutable($this->getStartsAt());
        $dayOfWeek = (int) $startsAt->format('w');
        $days = ($daysPerWeek + $dayOfWeek - $weekStartsAt) % $daysPerWeek;
        $startsAt->modify('-'.$days.' days');
        $startsAt->setTime(0, 0);

        return \DateTimeImmutable::createFromMutable($startsAt);
    }

    public function getEndOfWeek(int $weekStartsAt = 6): \DateTimeImmutable
    {
        $startsAt = \DateTime::createFromImmutable($this->getStartOfWeek($weekStartsAt));
        $endsAt = $startsAt->modify('+6 days')->setTime(23, 59, 59, 999999);

        return \DateTimeImmutable::createFromMutable($endsAt);
    }

    public function getStartOfMonth(): \DateTimeImmutable
    {
        $datetime = $this->getStartsAt()->format('Y-m-01 00:00:00');

        return new \DateTimeImmutable($datetime);
    }

    public function getEndOfMonth(): \DateTimeImmutable
    {
        $datetime = $this->getStartsAt()->format('Y-m-t 23:59:59.999999');

        return new \DateTimeImmutable($datetime);
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
