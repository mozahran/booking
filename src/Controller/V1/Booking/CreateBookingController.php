<?php

declare(strict_types=1);

namespace App\Controller\V1\Booking;

use App\Contract\Service\ChronoConductorInterface;
use App\Request\BookingRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CreateBookingController extends AbstractController
{
    public function __construct(
        private readonly ChronoConductorInterface $chronoConductor,
    ) {
    }

    #[Route(path: '/v1/booking', name: 'app_booking_create', methods: ['POST'])]
    #[IsGranted('MANAGE_BOOKING', subject: 'request', message: 'Access Denied!')]
    public function __invoke(
        BookingRequest $request,
    ): JsonResponse {
        $booking = $this->chronoConductor->createOrUpdate(
            request: $request,
            userId: $this->getUser()->getId(),
        );

        return $this->json([
            'data' => $booking->normalize(),
        ], status: Response::HTTP_CREATED);
    }
}
