<?php

declare(strict_types=1);

namespace App\Controller\V1\Booking;

use App\Contract\Resolver\BookingResolverInterface;
use App\Contract\Resolver\UserResolverInterface;
use App\Contract\Service\NexusInterface;
use App\Domain\DataObject\Booking\Booking;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ShowBookingController extends AbstractController
{
    public function __construct(
        private readonly BookingResolverInterface $bookingResolver,
        private readonly NexusInterface $nexus,
        private readonly UserResolverInterface $userResolver,
    ) {
    }

    #[Route(path: '/v1/booking/{bookingId}', name: 'app_booking_show', methods: ['GET'])]
    public function __invoke(
        int $bookingId,
    ): JsonResponse {
        $booking = $this->bookingResolver->resolve(
            id: $bookingId,
        );
        $user = null;
        if ($this->canViewUserData(booking: $booking)) {
            $user = $this->userResolver->resolve(id: $this->getUser()->getId());
        }

        return $this->json(
            data: [
                'booking' => $booking->normalize(),
                'user' => $user,
            ],
        );
    }

    private function canViewUserData(
        Booking $booking,
    ): bool {
        $authUser = $this->getUser();

        return $this->nexus->isBookingOwner($booking, $authUser)
            || $this->nexus->isAdmin($authUser);
    }
}
