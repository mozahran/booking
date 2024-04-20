<?php

namespace App\Contract\Service\BookingRule;

use App\Contract\DataObject\RuleInterface;
use App\Domain\DataObject\Set\BookingRuleSet;
use App\Domain\Enum\RuleType;
use App\Domain\Exception\RuleTypeMissingImplementationException;

interface RuleSmithInterface
{
    /**
     * @throws RuleTypeMissingImplementationException
     */
    public function parse(
        RuleType $type,
        string $rule,
    ): RuleInterface;

    /**
     * @return RuleInterface[]
     *
     * @throws RuleTypeMissingImplementationException
     */
    public function parseRuleSet(
        BookingRuleSet $ruleSet,
    ): array;
}
