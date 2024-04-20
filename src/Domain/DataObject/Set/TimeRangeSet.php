<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Set;

use App\Domain\DataObject\Booking\TimeRange;

/**
 * @method TimeRange|null first()
 * @method TimeRange|null last()
 * @method TimeRange[]    items()
 * @method add(TimeRange $item)
 * @method remove(TimeRange $item)
 */
class TimeRangeSet extends AbstractSet
{
}
