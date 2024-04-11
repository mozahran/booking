<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\DataObject\RuleInterface;
use App\Contract\Service\RuleSmithInterface;
use App\Domain\DataObject\Rule\Availability;
use App\Domain\DataObject\Rule\Buffer;
use App\Domain\DataObject\Rule\Deny;
use App\Domain\DataObject\Rule\Quota;
use App\Domain\DataObject\Rule\Window;
use App\Domain\Enum\RuleType;
use App\Domain\Exception\RuleTypeMissingImplemenationException;

class RuleSmith implements RuleSmithInterface
{
    public function parse(
        RuleType $type,
        string $rule,
    ): RuleInterface {
        $data = json_decode(json: $rule, associative: true);

        return match ($type) {
            RuleType::AVAILABILITY => Availability::denormalize($data),
            RuleType::BUFFER => Buffer::denormalize($data),
            RuleType::DENY => Deny::denormalize($data),
            RuleType::QUOTA => Quota::denormalize($data),
            RuleType::WINDOW => Window::denormalize($data),
            default => throw new RuleTypeMissingImplemenationException($type),
        };
    }
}
