<?php

declare(strict_types=1);

namespace App\Contract\Repository;

use App\Domain\DataObject\Booking\Occurrence;
use App\Domain\DataObject\Set\OccurrenceSet;
use App\Domain\Exception\OccurrenceNotFoundException;

interface OccurrenceRepositoryInterface
{
    /**
     * @throws OccurrenceNotFoundException
     */
    public function findOne(
        int $id,
    ): Occurrence;

    public function findMany(
        array $ids,
    ): OccurrenceSet;

    public function findManyByBooking(
        int $bookingId,
    ): OccurrenceSet;

    public function findForConflictDetection(
        int $spaceId,
        OccurrenceSet $occurrenceSet,
        ?int $id,
    ): OccurrenceSet;

    public function cancel(
        array $ids,
        int $cancellerId,
    ): void;

    public function cancelSelectedAndFollowing(
        int $id,
        int $bookingId,
        int $cancellerId,
    ): void;

    public function delete(
        array $ids,
    ): void;
}
