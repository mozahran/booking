<?php

declare(strict_types=1);

namespace App\Domain\Enum\Rule;

enum Predicate: int
{
    case MORE_THAN_STRICT = 1;
    case LESS_THAN = 2;
    case MORE_THAN_INCL_CURRENT_DAY = 3;
}
