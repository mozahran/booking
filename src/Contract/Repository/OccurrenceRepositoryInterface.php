<?php

declare(strict_types=1);

namespace App\Contract\Repository;

use App\Domain\DataObject\Booking\Occurrence;
use App\Domain\DataObject\Booking\TimeRange;
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

    /**
     * @param TimeRange[] $timeRanges
     */
    public function getTimeUsageByUserAndSpaceInGivenTimeRanges(
        int $spaceId,
        int $userId,
        array $timeRanges,
    ): int;

    public function getTimeUsageByUserAndSpace(
        int $userId,
        int $spaceId,
    ): int;

    /**
     * @param TimeRange[] $timeRanges
     */
    public function countByUserAndSpaceInGivenTimeRanges(
        int $spaceId,
        int $userId,
        array $timeRanges,
    ): int;

    public function countByUserAndSpace(
        int $userId,
        int $spaceId,
    ): int;
}
