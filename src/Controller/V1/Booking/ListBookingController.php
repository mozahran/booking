<?php

declare(strict_types=1);

namespace App\Controller\V1\Booking;

use App\Contract\Resolver\BookingResolverInterface;
use App\Contract\Resolver\SpaceResolverInterface;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\Exception\InvalidTimeRangeException;
use App\Request\BookingRequest;
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
        BookingRequest $request,
    ): JsonResponse {
        $now = new \DateTimeImmutable('now');
        $startsAt = $request->getStartsAt($now->format('Y-m-d 00:00:00'));
        $endsAt = $request->getEndsAt($now->format('Y-m-d 23:59:59'));
        $timeRange = new TimeRange(startsAt: $startsAt, endsAt: $endsAt);
        $this->validateRangeSelection($timeRange);
        $space = $this->spaceResolver->resolve(id: $request->getSpaceId());
        $bookingSet = $this->bookingResolver->resolveRange(
            spaceId: $space->getId(),
            timeRange: $timeRange,
        );

        return $this->json([
            'data' => $bookingSet->normalize(),
        ]);
    }

    /**
     * @throws InvalidTimeRangeException
     */
    private function validateRangeSelection(
        TimeRange $timeRange,
    ): void {
        $timeDiff = $timeRange->getStartsAt()->diff($timeRange->getEndsAt());
        $numberOfDays = date('t', $timeRange->getStartsAt()->getTimestamp());
        if ($timeDiff->days > $numberOfDays - 1) {
            throw new InvalidTimeRangeException(sprintf('Maximum time range is %d days!', $numberOfDays));
        }
    }
}
