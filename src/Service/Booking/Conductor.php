<?php

namespace App\Service\Booking;

use App\Builder\BookingBuilder;
use App\Builder\OccurrenceSetBuilder;
use App\Contract\Persistor\BookingPersistorInterface;
use App\Contract\Repository\OccurrenceRepositoryInterface;
use App\Contract\Resolver\BookingResolverInterface;
use App\Contract\Service\Booking\ConductorInterface;
use App\Contract\Service\JanitorInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Booking\RecurrenceRule;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\DataObject\Set\OccurrenceSet;
use App\Domain\Exception\TimeSlotNotAvailableException;
use App\Request\BookingRequest;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;

final class Conductor implements ConductorInterface
{
    public function __construct(
        private BookingResolverInterface $bookingResolver,
        private BookingPersistorInterface $bookingPersistor,
        private JanitorInterface $janitor,
        private EntityManagerInterface $entityManager,
        private OccurrenceRepositoryInterface $occurrenceRepository,
    ) {
    }

    public function upsert(
        BookingRequest $bookingRequest,
        int $userId = null,
    ): Booking {
        $bookingRequest->validate();

        $booking = null;
        if ($bookingRequest->getBookingId()) {
            $booking = $this->bookingResolver->resolve(id: $bookingRequest->getBookingId());
        }

        $existingOccurrenceSet = $this->getExistingOccurrences($booking);
        $timeRange = TimeRange::fromRequest($bookingRequest);
        $recurrenceRule = $this->createRecurrenceRule($bookingRequest, $booking);

        $occurrenceSet = (new OccurrenceSetBuilder())
            ->setExistingOccurrences($existingOccurrenceSet)
            ->setTimeRange($timeRange)
            ->setRule($recurrenceRule)
            ->build();

        $booking = (new BookingBuilder())
            ->setId($booking?->getId())
            ->setSpaceId($booking?->getSpaceId() ?? $bookingRequest->getSpaceId())
            ->setUserId($booking?->getUserId() ?? $userId)
            ->setTimeRange($timeRange)
            ->setRecurrenceRule($recurrenceRule)
            ->setOccurrenceSet($occurrenceSet)
            ->build();

        $this->validateTimeslots($booking);

        $this->entityManager->beginTransaction();
        try {
            $booking = $this->bookingPersistor->persist($booking);
            $this->janitor->cleanObsoleteOccurrences(
                existingOccurrenceSet: $existingOccurrenceSet,
                occurrenceSet: $occurrenceSet,
            );
            $this->entityManager->commit();

            return $booking;
        } catch (Throwable $exception) {
            $this->entityManager->rollback();
            throw $exception;
        }
    }

    /**
     * @throws TimeSlotNotAvailableException
     */
    private function validateTimeslots(
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
