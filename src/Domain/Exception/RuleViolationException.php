<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use App\Domain\DataObject\Booking\Occurrence;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\DataObject\Rule\Availability;
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
    ) {
        $message = sprintf(
            'Occurrences on %s is not allowed.',
            $occurrence->getTimeRange()->getStartsAt()->format('l'),
        );

        return new self(
            message: $message,
        );
    }
}
