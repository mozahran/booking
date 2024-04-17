<?php

declare(strict_types=1);

namespace App\Contract\Service\Booking;

use App\Contract\DataObject\TimeBoundedRuleInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Utils\RuleViolationList;

interface TimeWardenInterface
{
    public function validateBoundaries(
        Booking $booking,
        TimeBoundedRuleInterface $rule,
    ): RuleViolationList;
}
