<?php

declare(strict_types=1);

namespace App\Domain\Enum\Rule;

enum Operand: int
{
    case ITS_DURATION = 0;
    case INTERVAL_FROM_MIDNIGHT = 1;
    case INTERVAL_TO_MIDNIGHT = 2;
    case USER_ROLES = 3;
}
