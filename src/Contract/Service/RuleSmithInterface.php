<?php

namespace App\Contract\Service;

use App\Contract\DataObject\RuleInterface;
use App\Domain\Enum\RuleType;
use App\Domain\Exception\RuleTypeMissingImplemenationException;

interface RuleSmithInterface
{
    /**
     * @throws RuleTypeMissingImplemenationException
     */
    public function parse(
        RuleType $type,
        string $rule,
    ): RuleInterface;
}
