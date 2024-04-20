<?php

declare(strict_types=1);

namespace App\Contract\Persistor;

use App\Domain\DataObject\BookingRule;
use App\Domain\Exception\BookingRuleNotFoundException;

interface BookingRulePersistorInterface
{
    /**
     * @throws BookingRuleNotFoundException
     */
    public function persist(
        BookingRule $rule,
    ): BookingRule;
}
