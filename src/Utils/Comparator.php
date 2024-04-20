<?php

declare(strict_types=1);

namespace App\Utils;

use App\Contract\DataObject\TimeBoundedRuleInterface;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\Enum\Rule\Operator;

class Comparator
{
    public static function is(
        mixed $x,
        Operator $operator,
        mixed $y,
    ): bool {
        return match ($operator) {
            Operator::LESS_THAN => $x < $y,
            Operator::LESS_THAN_OR_EQUAL_TO => $x <= $y,
            Operator::GREATER_THAN => $x > $y,
            Operator::GREATER_THAN_OR_EQUAL_TO => $x >= $y,
            Operator::EQUAL_TO => $x === $y,
            Operator::NOT_EQUAL_TO => $x !== $y,
            Operator::NOT_MULTIPLE_OF => 0 === $y % $x,
            Operator::MULTIPLE_OF => 0 !== $y % $x,
            Operator::INSET => is_array($x) ? boolval(array_intersect($x, $y)) : in_array($x, $y),
            Operator::NOT_INSET => !in_array($x, $y),
        };
    }

    public static function isWithinWeekdayBoundaries(
        int $weekdayNumber,
        int $daysBitmask,
    ): bool {
        return 0 < ($daysBitmask & (1 << $weekdayNumber));
    }

    public static function isWithinTimeBoundaries(
        TimeRange $timeRange,
        TimeBoundedRuleInterface $rule,
    ): bool {
        $startDate = $timeRange->getStartsAt()->format('Y-m-d');
        // Subtract 1 second in case the booking ends at midnight
        $endDate = $timeRange->getEndsAt()->format('Y-m-d');
        if ($endDate !== $startDate) {
            $endsAt = \DateTime::createFromImmutable($timeRange->getEndsAt())->modify('-1 second');
            if ($endsAt->format('Y-m-d') !== $startDate) {
                return false;
            }
            $h = (int) $endsAt->format('G');
            $m = (int) $endsAt->format('i');
            $endMinutes = ($h * 60) + $m;
        } else {
            $endMinutes = $timeRange->getEndMinutes();
        }

        return $timeRange->getStartMinutes() >= $rule->getStartMinutes()
            && $endMinutes <= $rule->getEndMinutes();
    }
}
