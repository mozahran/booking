<?php

declare(strict_types=1);

namespace App\Contract\Validator;

use App\Contract\DataObject\RuleInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Utils\RuleViolationList;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(self::TAG)]
interface RuleValidatorInterface
{
    public const TAG = 'app.rule.validators';

    public function validate(
        Booking $booking,
        RuleInterface $rule,
    ): RuleViolationList;
}
