<?php

namespace App\Service;

use App\Builder\BookingBuilder;
use App\Builder\OccurrenceBuilder;
use App\Contract\Persistor\BookingPersistorInterface;
use App\Contract\Repository\OccurrenceRepositoryInterface;
use App\Contract\Resolver\BookingResolverInterface;
use App\Contract\Service\ChronoConductorInterface;
use App\Contract\Service\JanitorInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Booking\RecurrenceRule;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\DataObject\Set\OccurrenceSet;
use App\Domain\Exception\TimeSlotNotAvailableException;
use App\Request\BookingRequest;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ChronoConductor implements ChronoConductorInterface
{
    public function __construct(
        private BookingResolverInterface $bookingResolver,
        private BookingPersistorInterface $bookingPersistor,
        private JanitorInterface $janitor,
        private EntityManagerInterface $entityManager,
        private OccurrenceRepositoryInterface $occurrenceRepository,
    ) {
    }

    public function createOrUpdate(
        BookingRequest $request,
        ?int $userId = null,
    ): Booking {
        $request->validate();

        $booking = null;
        if ($request->getBookingId()) {
            $booking = $this->bookingResolver->resolve(id: $request->getBookingId());
        }

        $existingOccurrenceSet = $this->getExistingOccurrences($booking);
        $timeRange = TimeRange::fromRequest($request);
        $recurrenceRule = $this->createRecurrenceRule($request, $booking);

        $occurrenceSet = (new OccurrenceBuilder())
            ->setExistingOccurrences($existingOccurrenceSet)
            ->setTimeRange($timeRange)
            ->setRule($recurrenceRule)
            ->build();

        $booking = (new BookingBuilder())
            ->setId($booking?->getId())
            ->setSpaceId($booking?->getSpaceId() ?? $request->getSpaceId())
            ->setUserId($booking?->getUserId() ?? $userId)
            ->setTimeRange($timeRange)
            ->setRecurrenceRule($recurrenceRule)
            ->setOccurrenceSet($occurrenceSet)
            ->build();

        $this->validate($booking);
        $this->entityManager->beginTransaction();
        try {
            $booking = $this->bookingPersistor->persist($booking);
            $this->janitor->cleanObsoleteOccurrences(
                existingOccurrenceSet: $existingOccurrenceSet,
                occurrenceSet: $occurrenceSet,
            );
            $this->entityManager->commit();

            return $booking;
        } catch (\Throwable $exception) {
            $this->entityManager->rollback();
            throw $exception;
        }
    }

    /**
     * @throws TimeSlotNotAvailableException
     */
    private function validate(
        Booking $booking,
    ): void {
        if ($booking->getOccurrences()->isEmpty()) {
            return;
        }

        $existingOccurrences = $this->occurrenceRepository->findForConflictDetection(
            spaceId: $booking->getSpaceId(),
            occurrenceSet: $booking->getOccurrences(),
            id: $booking->getId(),
        );

        if (0 === $existingOccurrences->count()) {
            return;
        }

        throw new TimeSlotNotAvailableException($existingOccurrences);
    }

    private function getExistingOccurrences(
        ?Booking $booking,
    ): OccurrenceSet {
        return $booking ? clone $booking->getOccurrences() : new OccurrenceSet();
    }

    private function createRecurrenceRule(
        BookingRequest $request,
        ?Booking $booking,
    ): RecurrenceRule {
        return new RecurrenceRule(
            rule: $request->getRecurrenceRule(),
            excludedDates: $booking?->getBookingSpec()?->getRecurrenceRule()?->getExcludedDates() ?? [],
        );
    }
}
