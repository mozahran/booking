<?php

declare(strict_types=1);

namespace App\Validator\Rule;

use App\Contract\DataObject\RuleInterface;
use App\Contract\Service\TimeWardenInterface;
use App\Contract\Validator\RuleValidatorInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Rule\Quota;
use App\Utils\RuleViolationList;

final readonly class QuotaRuleValidator implements RuleValidatorInterface
{
    public function __construct(
        private TimeWardenInterface $timeWarden,
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
