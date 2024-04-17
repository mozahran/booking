<?php

namespace App\Validator;

use App\Contract\DataObject\RuleInterface;
use App\Contract\Service\Booking\TimeWardenInterface;
use App\Contract\Validator\RuleValidatorInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Rule\Availability;
use App\Utils\RuleViolationList;

final class AvailabilityRuleValidator implements RuleValidatorInterface
{
    public function __construct(
        private readonly TimeWardenInterface $timeWarden,
    ) {
    }

    public function validate(
        Booking $booking,
        Availability|RuleInterface $rule,
    ): RuleViolationList {
        return $this->timeWarden->validateBoundaries(
            booking: $booking,
            rule: $rule,
        );
    }
}
