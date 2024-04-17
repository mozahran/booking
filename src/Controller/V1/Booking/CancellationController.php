<?php

declare(strict_types=1);

namespace App\Controller\V1\Booking;

use App\Contract\Service\Booking\VortexInterface;
use App\Domain\Enum\CancellationIntent;
use App\Domain\Exception\AccessDeniedException;
use App\Domain\Exception\DataMismatchException;
use App\Domain\Exception\InvalidIntentException;
use App\Request\CancellationRequest;
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

    /**
     * @throws AccessDeniedException
     * @throws DataMismatchException
     * @throws InvalidIntentException
     */
    #[Route(path: '/v1/cancel', name: 'app_cancel', methods: ['PUT'])]
    #[IsGranted('IS_CANCELLER', subject: 'request', message: 'Access Denied!')]
    public function __invoke(
        CancellationRequest $request,
    ): JsonResponse {
        match ($request->getIntent()) {
            CancellationIntent::ALL => $this->vortex->cancelBookings(
                bookingIds: $request->getBookingIds(),
                user: $this->getUser(),
            ),
            CancellationIntent::SELECTED => $this->vortex->cancelOccurrences(
                occurrenceIds: $request->getOccurrenceIds(),
                user: $this->getUser(),
            ),
            CancellationIntent::SELECTED_AND_FOLLOWING => $this->vortex->cancelSelectedAndFollowingOccurrences(
                occurrenceId: current($request->getOccurrenceIds()),
                user: $this->getUser(),
            ),
        };

        return $this->json([]);
    }
}
