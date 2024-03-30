<?php

declare(strict_types=1);

namespace App\Contract\Resolver;

use App\Domain\DataObject\Booking\Occurrence;
use App\Domain\DataObject\Set\OccurrenceSet;
use App\Domain\Exception\OccurrenceNotFoundException;

interface OccurrenceResolverInterface
{
    /**
     * @throws OccurrenceNotFoundException
     */
    public function resolve(
        int $id,
    ): Occurrence;

    public function resolveMany(
        array $ids,
    ): OccurrenceSet;

    public function resolveByBooking(
        int $bookingId,
    ): OccurrenceSet;
}
