<?php

declare(strict_types=1);

namespace App\Controller\V1\Booking;

use App\Contract\Service\ConductorInterface;
use App\Request\BookingRequest;
use App\Security\BookingVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CreateBookingController extends AbstractController
{
    public function __construct(
        private readonly ConductorInterface $conductor,
    ) {
    }

    #[Route(path: '/v1/booking', name: 'app_booking_create', methods: ['POST'])]
    #[IsGranted(attribute: BookingVoter::MANAGE, subject: 'bookingRequest', message: 'Access Denied!')]
    public function __invoke(
        BookingRequest $bookingRequest,
    ): JsonResponse {
        $booking = $this->conductor->upsert(
            bookingRequest: $bookingRequest,
            userId: $this->getUser()->getId(),
        );

        return $this->json(
            data: [
                'data' => $booking->normalize(),
            ],
            status: Response::HTTP_CREATED,
        );
    }
}
