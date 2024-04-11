<?php

declare(strict_types=1);

namespace App\Domain\Enum\Rule;

enum Period: int
{
    case PER_DAY = 1;
    case PER_WEEK = 2;
    case PER_MONTH = 3;
    case AT_ANY_MOMENT = 4;
}
