<?php

declare(strict_types=1);

namespace App\Resolver;

use App\Contract\Repository\OccurrenceRepositoryInterface;
use App\Contract\Resolver\OccurrenceResolverInterface;
use App\Domain\DataObject\Booking\Occurrence;
use App\Domain\DataObject\Set\OccurrenceSet;

final class OccurrenceResolver implements OccurrenceResolverInterface
{
    public function __construct(
        private OccurrenceRepositoryInterface $occurrenceRepository,
    ) {
    }

    public function resolve(
        int $id,
    ): Occurrence {
        return $this->occurrenceRepository->findOne(id: $id);
    }

    public function resolveMany(
        array $ids,
    ): OccurrenceSet {
        return $this->occurrenceRepository->findMany(ids: $ids);
    }

    public function resolveByBooking(
        int $bookingId,
    ): OccurrenceSet {
        return $this->occurrenceRepository->findManyByBooking(bookingId: $bookingId);
    }
}
