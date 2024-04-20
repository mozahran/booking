<?php

namespace App\Domain\Enum;

use App\Domain\DataObject\Rule\Availability;
use App\Domain\DataObject\Rule\Buffer;
use App\Domain\DataObject\Rule\Deny;
use App\Domain\DataObject\Rule\Quota;
use App\Domain\DataObject\Rule\Repeat;
use App\Domain\DataObject\Rule\Window;
use App\Domain\Exception\RuleTypeMissingImplementationException;
use App\Domain\Exception\RuleTypeMissingRuleValidatorException;
use App\Validator\Rule\AvailabilityRuleValidator;
use App\Validator\Rule\BufferRuleValidator;
use App\Validator\Rule\DenyRuleValidator;
use App\Validator\Rule\QuotaRuleValidator;
use App\Validator\Rule\RepeatRuleValidator;
use App\Validator\Rule\WindowRuleValidator;

enum RuleType: string
{
    case AVAILABILITY = 'availability';
    case BUFFER = 'buffer';
    case DENY = 'deny';
    case QUOTA = 'quota';
    case WINDOW = 'window';
    case REPEAT = 'repeat';

    /**
     * @throws RuleTypeMissingImplementationException
     */
    public function rule(): string
    {
        return match ($this) {
            self::AVAILABILITY => Availability::class,
            self::BUFFER => Buffer::class,
            self::DENY => Deny::class,
            self::QUOTA => Quota::class,
            self::WINDOW => Window::class,
            self::REPEAT => Repeat::class,
            default => throw new RuleTypeMissingImplementationException($this),
        };
    }

    /**
     * @throws RuleTypeMissingRuleValidatorException
     */
    public function validator(): string
    {
        return match ($this) {
            self::AVAILABILITY => AvailabilityRuleValidator::class,
            self::BUFFER => BufferRuleValidator::class,
            self::DENY => DenyRuleValidator::class,
            self::QUOTA => QuotaRuleValidator::class,
            self::WINDOW => WindowRuleValidator::class,
            self::REPEAT => RepeatRuleValidator::class,
            default => throw new RuleTypeMissingRuleValidatorException($this),
        };
    }
}
