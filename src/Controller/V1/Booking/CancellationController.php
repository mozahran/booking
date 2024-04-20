<?php

declare(strict_types=1);

namespace App\Controller\V1\Booking;

use App\Contract\Service\VortexInterface;
use App\Domain\Enum\CancellationIntent;
use App\Request\CancellationRequest;
use App\Security\CancellationVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CancellationController extends AbstractController
{
    public function __construct(
        private readonly VortexInterface $vortex,
    ) {
    }

    #[Route(path: '/v1/cancel', name: 'app_cancel', methods: ['PUT'])]
    #[IsGranted(attribute: CancellationVoter::MANAGE, subject: 'cancellationRequest', message: 'Access Denied!')]
    public function __invoke(
        CancellationRequest $cancellationRequest,
    ): JsonResponse {
        match ($cancellationRequest->getIntent()) {
            CancellationIntent::ALL => $this->vortex->cancelBookings(
                bookingIds: $cancellationRequest->getBookingIds(),
                user: $this->getUser(),
            ),
            CancellationIntent::SELECTED => $this->vortex->cancelOccurrences(
                occurrenceIds: $cancellationRequest->getOccurrenceIds(),
                user: $this->getUser(),
            ),
            CancellationIntent::SELECTED_AND_FOLLOWING => $this->vortex->cancelSelectedAndFollowingOccurrences(
                occurrenceId: current($cancellationRequest->getOccurrenceIds()),
                user: $this->getUser(),
            ),
        };

        return $this->json([]);
    }
}
