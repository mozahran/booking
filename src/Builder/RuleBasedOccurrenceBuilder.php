<?php

namespace App\Builder;

use App\Domain\DataObject\Booking\OccurrenceProxyMap;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\Exception\InvalidTimeRangeException;
use RRule\RRule;

final readonly class RuleBasedOccurrenceBuilder extends OccurrenceBuilder
{
    private const OCCURRENCE_LIMIT = 30;

    private string $rule;
    /**
     * @var \DateTimeImmutable[]
     */
    private array $excludedDates;
    private int $recurrenceLimit;
    private TimeRange $timeRange;

    public function add(
        string $startsAt,
        string $endsAt,
        bool $cancelled = false,
        ?int $cancellerId = null,
        ?int $bookingId = null,
        ?int $id = null,
    ): void {
        throw new \LogicException('You cannot use ::add with RuleBasedOccurrenceBuilder.');
    }

    /**
     * @throws InvalidTimeRangeException
     */
    public function setTimeRange(
        string $startsAt,
        string $endsAt,
    ): void {
        $this->timeRange = new TimeRange(
            startsAt: $startsAt,
            endsAt: $endsAt,
        );
    }

    public function setRule(
        string $rule,
    ): void {
        $this->rule = $rule;
    }

    public function setExcludedDates(
        array $excludedDates,
    ): void {
        $this->excludedDates = $excludedDates;
    }

    public function setRecurrenceLimit(
        int $limit,
    ): void {
        $this->recurrenceLimit = $limit;
    }

    private function isExcluded(
        \DateTime $occurrence,
    ): bool {
        $dateTime = new \DateTimeImmutable($occurrence->format('Y-m-d 00:00:00'));

        return in_array(
            needle: $dateTime,
            haystack: $this->excludedDates,
        );
    }

    /**
     * @return \DateTime[]
     */
    private function createOccurrenceDates(): array
    {
        $rrule = new RRule(
            parts: $this->rule,
            dtstart: $this->timeRange->getStartsAt(),
        );
        $occurrenceLimit = $this->recurrenceLimit ?? self::OCCURRENCE_LIMIT;
        /** @var \DateTime[] $occurrences */
        $occurrences = $rrule->getOccurrences(limit: $occurrenceLimit);

        return $occurrences;
    }

    /**
     * @throws InvalidTimeRangeException
     */
    public function build(): OccurrenceProxyMap
    {
        $startTime = $this->timeRange->getStartTime();
        $endTime = $this->timeRange->getEndTime();
        $duration = $this->timeRange->getDuration();

        $occurrences = $this->createOccurrenceDates();
        foreach ($occurrences as $occurrence) {
            if ($this->isExcluded($occurrence)) {
                continue;
            }
            $startsAt = $occurrence->format(sprintf('Y-m-d %s', $startTime));
            $endsAt = $occurrence->modify(sprintf('+%d minutes', $duration))->format(sprintf('Y-m-d %s', $endTime));
            $occurrenceDto = $this->createOccurrence(
                startsAt: $startsAt,
                endsAt: $endsAt,
                cancelled: false,
            );
            $this->proxyMapBuilder->add(
                occurrence: $occurrenceDto,
            );
        }

        return $this->proxyMapBuilder->build();
    }
}
