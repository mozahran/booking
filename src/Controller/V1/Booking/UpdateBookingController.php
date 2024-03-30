<?php

declare(strict_types=1);

namespace App\Controller\V1\Booking;

use App\Contract\Service\ChronoConductorInterface;
use App\Request\BookingRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UpdateBookingController extends AbstractController
{
    public function __construct(
        private readonly ChronoConductorInterface $chronoConductor,
    ) {
    }

    #[Route(path: '/v1/booking/{bookingId}', name: 'app_booking_update', methods: ['PUT'])]
    #[IsGranted('MANAGE_BOOKING', subject: 'request', message: 'Access Denied!')]
    public function __invoke(
        int $bookingId,
        BookingRequest $request,
    ): JsonResponse {
        $booking = $this->chronoConductor->createOrUpdate(
            request: $request,
        );

        return $this->json([
            'data' => $booking->normalize(),
        ]);
    }
}
