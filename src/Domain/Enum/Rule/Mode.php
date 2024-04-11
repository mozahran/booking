<?php

declare(strict_types=1);

namespace App\Domain\Enum\Rule;

enum Mode: int
{
    case ALL_USERS = 0;
    case USERS_WITH_ANY_OF_TAGS = 1;
    case USERS_WITH_NONE_OF_TAGS = 2;
}
