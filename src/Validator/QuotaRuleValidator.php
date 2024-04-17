<?php

declare(strict_types=1);

namespace App\Validator;

use App\Contract\DataObject\RuleInterface;
use App\Contract\Service\Booking\TimeWardenInterface;
use App\Contract\Validator\RuleValidatorInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Rule\Quota;
use App\Domain\Exception\RuleViolationException;
use App\Utils\RuleViolationList;

final class QuotaRuleValidator implements RuleValidatorInterface
{
    public function __construct(
        private readonly TimeWardenInterface $timeWarden,
    ) {
    }

    public function validate(
        Booking $booking,
        Quota|RuleInterface $rule,
    ): RuleViolationList {
        $ruleViolationList = RuleViolationList::create();

        $timeBoundaryViolations = $this->timeWarden->validateBoundaries(
            booking: $booking,
            rule: $rule,
        );

        $ruleViolationList->merge($timeBoundaryViolations->all());

        return $ruleViolationList;
    }
}
