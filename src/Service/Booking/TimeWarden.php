<?php

declare(strict_types=1);

namespace App\Service\Booking;

use App\Contract\DataObject\TimeBoundedRuleInterface;
use App\Contract\Service\Booking\TimeWardenInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Booking\Occurrence;
use App\Domain\Exception\RuleViolationException;
use App\Utils\RuleViolationList;

class TimeWarden implements TimeWardenInterface
{
    public function validateBoundaries(
        Booking $booking,
        TimeBoundedRuleInterface $rule
    ): RuleViolationList {
        $ruleViolationList = RuleViolationList::create();
        $occurrences = $booking->getOccurrences()->items();
        foreach ($occurrences as $occurrence) {
            if (false === $this->isOccurrenceWithinTimeRange(occurrence: $occurrence, rule: $rule)) {
                $ruleViolationList->add(
                    violation: RuleViolationException::outsideAllowedTimeRange(occurrence: $occurrence, rule: $rule),
                );
            }
            if (false === $this->isOccurrenceWithinWeekdays(occurrence: $occurrence, rule: $rule)) {
                $ruleViolationList->add(
                    violation: RuleViolationException::outsideAllowedWeekays(occurrence: $occurrence, rule: $rule),
                );
            }
        }

        return $ruleViolationList;
    }

    private function isOccurrenceWithinWeekdays(
        Occurrence $occurrence,
        TimeBoundedRuleInterface $rule,
    ): bool {
        $timeRange = $occurrence->getTimeRange();

        return $timeRange->getStartMinutes() >= $rule->getStartMinutes()
            && $timeRange->getEndMinutes() <= $rule->getEndMinutes();
    }

    private function isOccurrenceWithinTimeRange(
        Occurrence $occurrence,
        TimeBoundedRuleInterface $rule,
    ): bool {
        $dayNumber = (int)$occurrence->getTimeRange()->getStartsAt()->format('w');

        return 0 < ($rule->getDaysBitmask() & (1 << $dayNumber));
    }
}
