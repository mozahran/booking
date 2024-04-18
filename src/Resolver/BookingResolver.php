<?php

declare(strict_types=1);

namespace App\Resolver;

use App\Contract\Repository\BookingRepositoryInterface;
use App\Contract\Resolver\BookingResolverInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\DataObject\Set\BookingSet;

final readonly class BookingResolver implements BookingResolverInterface
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository,
    ) {
    }

    public function resolve(
        int $id,
    ): Booking {
        return $this->bookingRepository->findOne(id: $id);
    }

    public function resolveMany(
        array $ids,
    ): BookingSet {
        return $this->bookingRepository->findMany(ids: $ids);
    }

    public function resolveRange(
        int $spaceId,
        TimeRange $timeRange,
    ): BookingSet {
        return $this->bookingRepository->findManyByRange(
            spaceId: $spaceId,
            timeRange: $timeRange,
        );
    }
}
