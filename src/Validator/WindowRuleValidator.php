<?php

declare(strict_types=1);

namespace App\Validator;

use App\Contract\DataObject\RuleInterface;
use App\Contract\Validator\RuleValidatorInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Rule\Window;

class WindowRuleValidator implements RuleValidatorInterface
{
    public function validate(
        Booking $booking,
        Window|RuleInterface $rule,
    ): array {
        return [];
    }
}
