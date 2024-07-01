<?php

declare(strict_types=1);

namespace App\Controller\V1\Space;

use App\Contract\Resolver\BookingResolverInterface;
use App\Contract\Resolver\SpaceResolverInterface;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\Exception\InvalidTimeRangeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ShowSpaceController extends AbstractController
{
    public function __construct(
        private readonly SpaceResolverInterface $spaceResolver,
        private readonly BookingResolverInterface $bookingResolver,
    ) {
    }

    #[Route(path: '/v1/space/{spaceId}', name: 'app_space_show', methods: ['GET'])]
    public function __invoke(
        int $spaceId,
    ): JsonResponse {
        $space = $this->spaceResolver->resolve(
            id: $spaceId,
        );

        try {
            // start accepting start & end dates from request and use them to show bookings
            $timeRange = TimeRange::today();
            $bookings = $this->bookingResolver->resolveRange(
                spaceId: $spaceId,
                timeRange: $timeRange,
            );
        } catch (InvalidTimeRangeException) {
            $bookings = [];
        }

        return $this->json(
            data: [
                'space' => $space->normalize(),
                'bookings' => $bookings,
            ],
        );
    }
}
