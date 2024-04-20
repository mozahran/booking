<?php

declare(strict_types=1);

namespace App\Service\BookingRule;

use App\Contract\DataObject\TimeBoundedRuleInterface;
use App\Contract\Service\BookingRule\TimeWardenInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Booking\Occurrence;
use App\Domain\Exception\RuleViolationException;
use App\Utils\Comparator;
use App\Utils\RuleViolationList;

class TimeWarden implements TimeWardenInterface
{
    public function validateBoundaries(
        Booking $booking,
        TimeBoundedRuleInterface $rule,
    ): RuleViolationList {
        $ruleViolationList = RuleViolationList::empty();
        $occurrences = $booking->getOccurrences()->items();
        foreach ($occurrences as $occurrence) {
            // Weekday boundaries validation
            $violation = $this->validateWeekdayBoundaries(
                occurrence: $occurrence,
                rule: $rule,
            );
            if (null !== $violation) {
                $ruleViolationList->add($violation);
            }
            // Time boundaries validation
            $violation = $this->validateTimeBoundaries(
                occurrence: $occurrence,
                rule: $rule,
            );
            if (null !== $violation) {
                $ruleViolationList->add($violation);
            }
        }

        return $ruleViolationList;
    }

    private function validateWeekdayBoundaries(
        Occurrence $occurrence,
        TimeBoundedRuleInterface $rule,
    ): ?RuleViolationException {
        $isWithinWeekdayBoundaries = Comparator::isWithinWeekdayBoundaries(
            weekdayNumber: $occurrence->getTimeRange()->getWeekdayNumber(),
            daysBitmask: $rule->getDaysBitmask(),
        );

        if ($isWithinWeekdayBoundaries) {
            return null;
        }

        return RuleViolationException::outsideWeekdayBoundaries(
            occurrence: $occurrence,
            rule: $rule,
        );
    }

    private function validateTimeBoundaries(
        Occurrence $occurrence,
        TimeBoundedRuleInterface $rule,
    ): ?RuleViolationException {
        $isWithinTimeBoundaries = Comparator::isWithinTimeBoundaries(
            timeRange: $occurrence->getTimeRange(),
            rule: $rule,
        );

        if ($isWithinTimeBoundaries) {
            return null;
        }

        return RuleViolationException::outsideTimeBoundaries(
            occurrence: $occurrence,
            rule: $rule,
        );
    }
}
