<?php

declare(strict_types=1);

namespace App\Domain\Enum\Rule;

enum Predicate: int
{
    case MORE_THAN_STRICT = 1; // target: days
    case LESS_THAN = 2; // target: hours
    case MORE_THAN_INCLUDING_TODAY = 3; // target: days

    private const MINUTES_IN_HOUR = 60;
    public const MINUTES_IN_DAY = 1440;
    private const MINUTES_IN_TWO_DAYS = 2880;

    public function coefficient(): int
    {
        return match ($this) {
            self::MORE_THAN_STRICT => self::MINUTES_IN_DAY,
            self::MORE_THAN_INCLUDING_TODAY => self::MINUTES_IN_TWO_DAYS,
            default => self::MINUTES_IN_HOUR,
        };
    }
}
