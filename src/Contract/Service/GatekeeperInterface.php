<?php

declare(strict_types=1);

namespace App\Contract\Service;

use App\Contract\DataObject\RuleInterface;
use App\Domain\DataObject\Booking\Booking;

interface GatekeeperInterface
{
    /**
     * @param RuleInterface[] $rules
     */
    public function validate(
        array $rules,
        Booking $booking,
    ): void;
}
