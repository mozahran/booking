<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use App\Contract\DataObject\TimeBoundedRuleInterface;
use App\Domain\DataObject\Booking\Occurrence;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\DataObject\Rule\Quota;
use App\Domain\Enum\Rule\Operator;
use App\Domain\Enum\Rule\Predicate;
use Symfony\Component\HttpFoundation\Response;

class RuleViolationException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message, Response::HTTP_BAD_REQUEST);
    }

    public static function outsideTimeBoundaries(
        Occurrence $occurrence,
        TimeBoundedRuleInterface $rule,
    ): self {
        $message = sprintf(
            'Occurrence must be between %s and %s. Slot %s is not possible.',
            date('H:i', $rule->getStartMinutes() * 60),
            date('H:i', $rule->getEndMinutes() * 60),
            $occurrence->getTimeRange()->getStartsAt()->format(TimeRange::DATETIME_FORMAT),
        );

        return new self(
            message: $message,
        );
    }

    public static function outsideWeekdayBoundaries(
        Occurrence $occurrence,
        TimeBoundedRuleInterface $rule,
    ): self {
        $timeRange = $occurrence->getTimeRange();
        $startsAt = $timeRange->getStartsAt();
        $endsAt = $timeRange->getEndsAt();
        $message = sprintf(
            '%s, from %s to %s is not allowed. (Allowed range: %s to %s)',
            $startsAt->format('D, M d'),
            $startsAt->format('H:i'),
            $endsAt->format('H:i'),
            gmdate('H:i', $rule->getStartMinutes() * 60),
            gmdate('H:i', $rule->getEndMinutes() * 60),
        );

        return new self(
            message: $message,
        );
    }

    public static function windowLessThan(int $value): self
    {
        $message = sprintf(
            'You cannot book a slot before %d hour(s) from start time',
            $value / Predicate::LESS_THAN->coefficient(),
        );

        return new self(
            message: $message,
        );
    }

    public static function windowMoreThanStrict(int $value): self
    {
        $message = sprintf(
            'You cannot book a slot before exactly %d hour(s) from start time',
            $value / Predicate::MORE_THAN_STRICT->coefficient(),
        );

        return new self(
            message: $message,
        );
    }

    public static function windowMoreThanIncludingToday(int $value): self
    {
        $message = sprintf(
            'You cannot book a slot before %d day(s) including today from start time',
            $value / Predicate::MORE_THAN_INCLUDING_TODAY->coefficient(),
        );

        return new self(
            message: $message,
        );
    }

    public static function buffer(int $value): self
    {
        $message = sprintf('You cannot book a slot before/after %d minute(s) from other bookings', $value);

        return new self(
            message: $message,
        );
    }

    public static function durationIs(Operator $operator, mixed $value): self
    {
        $message = sprintf(
            'You cannot book a slot for a duration that is %s %s.',
            $operator->caption(),
            self::valueToString($value),
        );

        return new self(
            message: $message,
        );
    }

    public static function intervalFromMidnight(Operator $operator, mixed $value): self
    {
        $message = sprintf(
            'You cannot book a slot after midnight that is %s %s minutes.',
            $operator->caption(),
            self::valueToString($value),
        );

        return new self(
            message: $message,
        );
    }

    public static function intervalToMidnight(Operator $operator, mixed $value): self
    {
        $message = sprintf(
            'You cannot book a slot with an interval that is %s %s minutes to midnight.',
            $operator->caption(),
            self::valueToString($value),
        );

        return new self(
            message: $message,
        );
    }

    public static function userRoles(): self
    {
        return new self(
            message: 'You cannot book a slot with your set of roles.',
        );
    }

    public static function quota(Quota $rule): self
    {
        return new self(
            message: sprintf('Exceeded allowed quota (value: %s)!', $rule->getValue()),
        );
    }

    private static function valueToString(mixed $value): string
    {
        return is_array($value) ? '['.implode(', ', $value).']' : strval($value);
    }
}
