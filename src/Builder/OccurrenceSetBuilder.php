<?php

declare(strict_types=1);

namespace App\Builder;

use App\Domain\DataObject\Booking\Occurrence;
use App\Domain\DataObject\Booking\RecurrenceRule;
use App\Domain\DataObject\Booking\Status;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\DataObject\Set\OccurrenceSet;
use App\Domain\Exception\InvalidTimeRangeException;
use RRule\RRule;

final class OccurrenceSetBuilder
{
    private OccurrenceSet $occurrenceSet;
    private OccurrenceSet $existingOccurrences;
    private TimeRange $timeRange;
    private ?RecurrenceRule $rule = null;
    /**
     * @var \DateTimeImmutable[]
     */
    private array $excludedDates = [];
    private int $recurrenceLimit = 30;

    public function __construct()
    {
        $this->occurrenceSet = new OccurrenceSet();
        $this->existingOccurrences = new OccurrenceSet();
    }

    /**
     * @throws InvalidTimeRangeException
     */
    public function add(
        string $startsAt,
        string $endsAt,
        bool $cancelled = false,
        ?int $cancellerId = null,
        ?int $bookingId = null,
        ?int $id = null,
    ): self {
        $timeRange = new TimeRange(
            startsAt: $startsAt,
            endsAt: $endsAt,
        );
        $status = new Status(
            cancelled: $cancelled,
            cancellerId: $cancellerId,
        );
        $occurrence = new Occurrence(
            timeRange: $timeRange,
            status: $status,
            bookingId: $bookingId,
            id: $id,
        );

        $this->occurrenceSet->add($occurrence);

        return $this;
    }

    public function setTimeRange(
        TimeRange $timeRange,
    ): self {
        $this->timeRange = $timeRange;

        return $this;
    }

    public function setRule(
        RecurrenceRule $rule,
    ): self {
        $this->rule = $rule;
        $this->excludedDates = $rule->getExcludedDates();

        return $this;
    }

    public function setRecurrenceLimit(
        int $limit,
    ): self {
        $this->recurrenceLimit = $limit;

        return $this;
    }

    public function setExistingOccurrences(
        OccurrenceSet $occurrences,
    ): self {
        $this->existingOccurrences = $occurrences;

        return $this;
    }

    /**
     * @throws InvalidTimeRangeException
     */
    public function build(): OccurrenceSet
    {
        if ($this->isMissingRule() && isset($this->timeRange)) {
            $occurrence = $this->createOccurrence($this->timeRange);
            $this->occurrenceSet->add(item: $occurrence);
        }

        if ($this->isNotMissingRule()) {
            $occurrences = $this->buildOccurrencesFromRule();
            foreach ($occurrences as $occurrence) {
                $this->occurrenceSet->add(item: $occurrence);
            }
        }

        return $this->occurrenceSet;
    }

    private function createOccurrence(
        TimeRange $timeRange,
    ): Occurrence {
        /** @var Occurrence $existingOccurrence */
        $existingOccurrence = $this->existingOccurrences->find(
            dateString: $timeRange->getDateString(),
        );
        $status = new Status(
            cancelled: $existingOccurrence?->getStatus()->isCancelled() ?? false,
            cancellerId: $existingOccurrence?->getStatus()->getCancellerId(),
        );

        return new Occurrence(
            timeRange: $timeRange,
            status: $status,
            bookingId: $existingOccurrence?->getBookingId(),
            id: $existingOccurrence?->getId(),
        );
    }

    /**
     * @return Occurrence[]
     *
     * @throws InvalidTimeRangeException
     */
    private function buildOccurrencesFromRule(): array
    {
        $result = [];
        $rrule = new RRule(
            parts: $this->rule->getRule(),
            dtstart: $this->timeRange->getStartsAt(),
        );
        $occurrences = $rrule->getOccurrences(limit: $this->recurrenceLimit);
        foreach ($occurrences as $occurrence) {
            $dateTime = new \DateTimeImmutable($occurrence->format('Y-m-d 00:00:00'));
            if ($this->isExcluded($dateTime)) {
                continue;
            }
            $startsAtFormatted = $occurrence
                ->format(sprintf('Y-m-d %s', $this->timeRange->getStartTime()));
            $endsAtFormatted = $occurrence
                ->modify(sprintf('+%d minutes', $this->timeRange->getDuration()))
                ->format(sprintf('Y-m-d %s', $this->timeRange->getEndTime()));
            $timeRange = new TimeRange(
                startsAt: $startsAtFormatted,
                endsAt: $endsAtFormatted,
            );
            $result[] = $this->createOccurrence($timeRange);
        }

        return $result;
    }

    private function isExcluded(
        \DateTimeImmutable $dateTime,
    ): bool {
        return in_array(
            needle: $dateTime,
            haystack: $this->excludedDates,
        );
    }

    private function isMissingRule(): bool
    {
        return null !== $this->rule && null === $this->rule->getRule();
    }

    private function isNotMissingRule(): bool
    {
        return null !== $this->rule && null !== $this->rule->getRule();
    }
}
