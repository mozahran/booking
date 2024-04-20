<?php

declare(strict_types=1);

namespace App\Contract\Translator;

use App\Domain\DataObject\BookingRule;
use App\Domain\DataObject\Set\BookingRuleSet;
use App\Entity\BookingRuleEntity;

interface BookingRuleTranslatorInterface
{
    public function toBookingRule(
        BookingRuleEntity $entity,
    ): BookingRule;

    public function toBookingRuleEntity(
        BookingRule $bookingRule,
    ): BookingRuleEntity;

    /**
     * @param BookingRuleEntity[] $entities
     */
    public function toBookingRuleSet(
        array $entities,
    ): BookingRuleSet;
}
