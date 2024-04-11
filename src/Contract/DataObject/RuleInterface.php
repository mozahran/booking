<?php

declare(strict_types=1);

namespace App\Contract\DataObject;

use App\Domain\Enum\RuleType;

interface RuleInterface
{
    public function getType(): RuleType;
}
