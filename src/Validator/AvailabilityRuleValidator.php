<?php

namespace App\Validator;

use App\Contract\DataObject\RuleInterface;
use App\Contract\Validator\RuleValidatorInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Booking\Occurrence;
use App\Domain\DataObject\Rule\Availability;
use App\Domain\Exception\RuleViolationException;

final class AvailabilityRuleValidator implements RuleValidatorInterface
{
    /**
     * @return RuleViolationException[]
     */
    public function validate(
        Booking $booking,
        Availability|RuleInterface $rule,
    ): array {
        if ($this->shouldIgnoreValidation($rule, $booking)) {
            return [];
        }

        $violations = [];
        $occurrences = $booking->getOccurrences()->items();
        foreach ($occurrences as $occurrence) {
            if (false === $this->isWithinAllowedTimeRange($occurrence, $rule)) {
                $violations[] = RuleViolationException::outsideAllowedTimeRange($occurrence, $rule);
            }
            if (false === $this->isWithinAllowedWeekdays($occurrence, $rule)) {
                $violations[] = RuleViolationException::outsideAllowedDayRange($occurrence, $rule);
            }
        }

        return $violations;
    }

    private function shouldIgnoreValidation(
        Availability $rule,
        Booking $booking,
    ): bool {
        return true === $this->isTargetingSpecificSpaces($rule)
            && false === $this->isRuleApplicableToBooking($rule, $booking);
    }

    private function isRuleApplicableToBooking(
        Availability $rule,
        Booking $booking,
    ): bool {
        $spaceIds = $rule->getSpaceIds();

        return in_array($booking->getSpaceId(), $spaceIds);
    }

    private function isTargetingSpecificSpaces(
        Availability $rule,
    ): bool {
        return is_array($rule->getSpaceIds());
    }

    private function isWithinAllowedTimeRange(
        Occurrence $occurrence,
        Availability $rule,
    ): bool {
        $timeRange = $occurrence->getTimeRange();

        return $timeRange->getStartMinutes() >= $rule->getStartMinutes()
            && $timeRange->getEndMinutes() <= $rule->getEndMinutes();
    }

    private function isWithinAllowedWeekdays(
        Occurrence $occurrence,
        Availability $rule,
    ): bool {
        $dayNumber = (int) $occurrence->getTimeRange()->getStartsAt()->format('w');

        return 0 < ($rule->getDaysBitmask() & (1 << $dayNumber));
    }
}
