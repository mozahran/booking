<?php

declare(strict_types=1);

namespace App\Domain\Enum\Rule;

enum Operator: int
{
    case LESS_THAN = 0;
    case LESS_THAN_OR_EQUAL_TO = 1;

    case GREATER_THAN = 2;
    case GREATER_THAN_OR_EQUAL_TO = 3;

    case EQUAL_TO = 4;
    case NOT_EQUAL_TO = 5;

    case NOT_MULTIPLE_OF = 6;
    case IS_MULTIPLE_OF = 7;

    case INSET = 9;
    case NOT_INSET = 10;
}
