<?php

declare(strict_types=1);

namespace App\Domain\Enum;

enum CancellationIntent: string
{
    case ALL = 'all';
    case SELECTED_AND_FOLLOWING = 'selected_and_following';
    case SELECTED = 'selected';
}
