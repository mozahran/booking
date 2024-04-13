<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use App\Domain\DataObject\Booking\Occurrence;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\DataObject\Rule\Availability;
use App\Domain\Enum\Rule\Predicate;
use Symfony\Component\HttpFoundation\Response;

class RuleViolationException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message, Response::HTTP_BAD_REQUEST);
    }

    public static function outsideAllowedTimeRange(
        Occurrence $occurrence,
        Availability $rule,
    ): self {
        $message = sprintf(
            'Occurrence must be between %s and %s. Slot %s is not possible.',
            date('H:i', $rule->getStartMinutes() * 60),
            date('H:i', $rule->getEndMinutes() * 60),
            $occurrence->getTimeRange()->getStartsAt()->format(TimeRange::SHORT_FORMAT),
        );

        return new self(
            message: $message,
        );
    }

    public static function outsideAllowedDayRange(
        Occurrence $occurrence,
        Availability $rule,
    ): self {
        $message = sprintf(
            'Occurrences on %s is not allowed.',
            $occurrence->getTimeRange()->getStartsAt()->format('l'),
        );

        return new self(
            message: $message,
        );
    }

    public static function windowLessThan(int $value): self
    {
        $message = sprintf('You cannot book a slot before %d hour(s) from start time', $value / Predicate::LESS_THAN->coefficient());

        return new self(
            message: $message,
        );
    }

    public static function windowMoreThanStrict(int $value): self
    {
        $message = sprintf('You cannot book a slot before exactly %d hour(s) from start time', $value / Predicate::MORE_THAN_STRICT->coefficient());

        return new self(
            message: $message,
        );
    }

    public static function windowMoreThanIncludingToday(int $value): self
    {
        $message = sprintf('You cannot book a slot before %d day(s) including today from start time', $value / Predicate::MORE_THAN_INCLUDING_TODAY->coefficient());

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
}
