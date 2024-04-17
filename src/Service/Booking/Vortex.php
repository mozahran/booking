<?php

declare(strict_types=1);

namespace App\Service\Booking;

use App\Contract\Repository\BookingRepositoryInterface;
use App\Contract\Repository\OccurrenceRepositoryInterface;
use App\Contract\Resolver\OccurrenceResolverInterface;
use App\Contract\Service\Booking\VortexInterface;
use App\Domain\Exception\OccurrenceNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

final class Vortex implements VortexInterface
{
    public function __construct(
        private OccurrenceResolverInterface $occurrenceResolver,
        private BookingRepositoryInterface $bookingRepository,
        private OccurrenceRepositoryInterface $occurrenceRepository,
    ) {
    }

    public function cancelBookings(
        array $bookingIds,
        UserInterface $user,
    ): void {
        $this->bookingRepository->cancel(
            ids: $bookingIds,
            cancellerId: $user->getId(),
        );
    }

    public function cancelOccurrences(
        array $occurrenceIds,
        UserInterface $user,
    ): void {
        $this->occurrenceRepository->cancel(
            ids: $occurrenceIds,
            cancellerId: $user->getId(),
        );
    }

    public function cancelSelectedAndFollowingOccurrences(
        int $occurrenceId,
        UserInterface $user,
    ): void {
        try {
            $occurrence = $this->occurrenceResolver->resolve(id: $occurrenceId);
            $this->occurrenceRepository->cancelSelectedAndFollowing(
                id: $occurrenceId,
                bookingId: $occurrence->getBookingId(),
                cancellerId: $user->getId(),
            );
        } catch (OccurrenceNotFoundException) {
            return;
        }
    }
}
