<?php

declare(strict_types=1);

namespace App\Validator\Rule;

use App\Contract\DataObject\RuleInterface;
use App\Contract\Validator\RuleValidatorInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Rule\Repeat;
use App\Utils\RuleViolationList;

final readonly class RepeatRuleValidator implements RuleValidatorInterface
{
    public function __construct(
    ) {
    }

    public function validate(
        Booking $booking,
        Repeat|RuleInterface $rule,
    ): RuleViolationList {
        $ruleViolationList = RuleViolationList::empty();

        return $ruleViolationList;
    }
}
