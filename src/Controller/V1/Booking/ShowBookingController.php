<?php

declare(strict_types=1);

namespace App\Controller\V1\Booking;

use App\Contract\Resolver\BookingResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ShowBookingController extends AbstractController
{
    public function __construct(
        private readonly BookingResolverInterface $bookingResolver,
    ) {
    }

    #[Route(path: '/v1/booking/{bookingId}', name: 'app_booking_show', methods: ['GET'])]
    public function __invoke(
        int $bookingId,
    ): JsonResponse {
        $booking = $this->bookingResolver->resolve(id: $bookingId);

        return $this->json([
            'data' => $booking->normalize(),
        ]);
    }
}
