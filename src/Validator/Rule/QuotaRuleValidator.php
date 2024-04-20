<?php

declare(strict_types=1);

namespace App\Validator\Rule;

use App\Contract\DataObject\RuleInterface;
use App\Contract\Repository\OccurrenceRepositoryInterface;
use App\Contract\Utils\TimeRangeSmithInterface;
use App\Contract\Validator\RuleValidatorInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\DataObject\Rule\Quota;
use App\Domain\DataObject\Set\OccurrenceSet;
use App\Domain\Enum\Rule\AggregationMetric;
use App\Domain\Enum\Rule\Operator;
use App\Domain\Enum\Rule\Period;
use App\Domain\Exception\RuleViolationException;
use App\Utils\Comparator;
use App\Utils\RuleViolationList;

final readonly class QuotaRuleValidator implements RuleValidatorInterface
{
    public function __construct(
        private OccurrenceRepositoryInterface $occurrenceRepository,
        private TimeRangeSmithInterface $timeRangeSmith,
    ) {
    }

    public function validate(
        Booking $booking,
        Quota|RuleInterface $rule,
    ): RuleViolationList {
        $ruleViolationList = RuleViolationList::empty();

        $consumedQuota = match ($rule->getAggregationMetric()) {
            AggregationMetric::TIME_USAGE_MAXIMUM => $this->aggregateTimeUsagePerPeriod(
                rule: $rule,
                booking: $booking,
            ),
            AggregationMetric::BOOKING_COUNT_MAXIMUM => $this->aggregateBookingCountPerPeriod(
                rule: $rule,
                booking: $booking,
            ),
        };

        if (Comparator::is(x: $consumedQuota, operator: Operator::GREATER_THAN_OR_EQUAL_TO, y: $rule->getValue())) {
            $ruleViolationException = RuleViolationException::quota($rule);
            $ruleViolationList->add($ruleViolationException);
        }

        return $ruleViolationList;
    }

    private function aggregateTimeUsagePerPeriod(
        Quota $rule,
        Booking $booking,
    ): int {
        $matchingOccurrenceSet = $this->findOccurrencesMatchingRule(
            occurrenceSet: $booking->getOccurrences(),
            rule: $rule,
            spaceId: $booking->getSpaceId(),
        );

        if (Period::AT_ANY_MOMENT !== $rule->getPeriod() && $matchingOccurrenceSet->isEmpty()) {
            return 0;
        }

        $timeRanges = $this->getExtendedTimeRanges(
            rule: $rule,
            timeRanges: $matchingOccurrenceSet->timeRanges(),
        );

        $timeUsage = match ($rule->getPeriod()) {
            Period::AT_ANY_MOMENT => $this->occurrenceRepository->getTimeUsageByUserAndSpace(
                userId: $booking->getUserId(),
                spaceId: $booking->getSpaceId(),
            ),
            default => $this->occurrenceRepository->getTimeUsageByUserAndSpaceInGivenTimeRanges(
                spaceId: $booking->getSpaceId(),
                userId: $booking->getUserId(),
                timeRanges: $timeRanges,
            ),
        };

        return $timeUsage + $matchingOccurrenceSet->duration();
    }

    private function aggregateBookingCountPerPeriod(
        Quota $rule,
        Booking $booking,
    ): int {
        $matchingOccurrenceSet = $this->findOccurrencesMatchingRule(
            occurrenceSet: $booking->getOccurrences(),
            rule: $rule,
            spaceId: $booking->getSpaceId(),
        );

        if (Period::AT_ANY_MOMENT !== $rule->getPeriod() && $matchingOccurrenceSet->isEmpty()) {
            return 0;
        }

        $extendedTimeRanges = $this->getExtendedTimeRanges(
            rule: $rule,
            timeRanges: $matchingOccurrenceSet->timeRanges(),
        );

        $occurrencesCount = match ($rule->getPeriod()) {
            Period::AT_ANY_MOMENT => $this->occurrenceRepository->countByUserAndSpace(
                userId: $booking->getUserId(),
                spaceId: $booking->getSpaceId(),
            ),
            default => $this->occurrenceRepository->countByUserAndSpaceInGivenTimeRanges(
                spaceId: $booking->getSpaceId(),
                userId: $booking->getUserId(),
                timeRanges: $extendedTimeRanges,
            ),
        };

        return $occurrencesCount + $matchingOccurrenceSet->count();
    }

    private function findOccurrencesMatchingRule(
        OccurrenceSet $occurrenceSet,
        Quota $rule,
        int $spaceId,
    ): OccurrenceSet {
        $result = new OccurrenceSet();
        $occurrences = $occurrenceSet->items();
        foreach ($occurrences as $occurrence) {
            $weekday = $occurrence->getTimeRange()->getWeekdayNumber();
            if (false === Comparator::isWithinWeekdayBoundaries(weekdayNumber: $weekday, daysBitmask: $rule->getDaysBitmask())) {
                continue;
            }
            if (false === Comparator::isWithinTimeBoundaries(timeRange: $occurrence->getTimeRange(), rule: $rule)) {
                continue;
            }
            if (null !== $rule->getSpaceIds() && !in_array($spaceId, $rule->getSpaceIds())) {
                continue;
            }
            $result->add($occurrence);
        }

        return $result;
    }

    /**
     * @param TimeRange[] $timeRanges
     *
     * @return TimeRange[]
     */
    private function getExtendedTimeRanges(
        Quota $rule,
        array $timeRanges,
    ): array {
        return match ($rule->getPeriod()) {
            Period::PER_DAY => $this->timeRangeSmith->extendToDayRanges($timeRanges),
            Period::PER_WEEK => $this->timeRangeSmith->extendToWeekRanges($timeRanges),
            Period::PER_MONTH => $this->timeRangeSmith->extendToMonthRanges($timeRanges),
            default => [],
        };
    }
}
