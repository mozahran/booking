<?php

declare(strict_types=1);

namespace App\Domain\Enum\Rule;

enum AggregationMetric: int
{
    case TIME_USAGE_MAXIMUM = 1;
    case BOOKING_COUNT_MAXIMUM = 2;
}
