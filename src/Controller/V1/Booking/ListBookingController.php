<?php

declare(strict_types=1);

namespace App\Controller\V1\Booking;

use App\Contract\Resolver\BookingResolverInterface;
use App\Contract\Resolver\SpaceResolverInterface;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\Exception\InvalidTimeRangeException;
use App\Request\BookingRequest;
use App\Utils\TimeDiff;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ListBookingController extends AbstractController
{
    public function __construct(
        private readonly SpaceResolverInterface $spaceResolver,
        private readonly BookingResolverInterface $bookingResolver,
    ) {
    }

    #[Route(path: '/v1/booking', name: 'app_booking_list', methods: ['GET'])]
    public function __invoke(
        BookingRequest $bookingRequest,
    ): JsonResponse {
        $timeRange = TimeRange::withDefault(
            startsAt: $bookingRequest->getStartsAt(),
            endsAt: $bookingRequest->getEndsAt(),
        );
        $this->validateTimeRange(
            timeRange: $timeRange,
        );
        $space = $this->spaceResolver->resolve(
            id: $bookingRequest->getSpaceId(),
        );
        $bookingSet = $this->bookingResolver->resolveRange(
            spaceId: $space->getId(),
            timeRange: $timeRange,
        );

        return $this->json(
            data: [
                'data' => $bookingSet->normalize(),
            ],
        );
    }

    /**
     * @throws InvalidTimeRangeException
     */
    private function validateTimeRange(
        TimeRange $timeRange,
    ): void {
        $diffInDays = TimeDiff::days(
            first: $timeRange->getStartsAt(),
            second: $timeRange->getEndsAt(),
        );
        $numberOfDays = (int) $timeRange->getStartsAt()->format('t');
        if ($diffInDays > 31) {
            throw new InvalidTimeRangeException(message: sprintf('Maximum allowed time range is %d days', $numberOfDays));
        }
    }
}
