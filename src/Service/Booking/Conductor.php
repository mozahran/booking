<?php

namespace App\Service\Booking;

use App\Builder\BookingBuilder;
use App\Builder\OccurrenceSetBuilder;
use App\Contract\Persistor\BookingPersistorInterface;
use App\Contract\Resolver\BookingResolverInterface;
use App\Contract\Resolver\BookingRuleResolverInterface;
use App\Contract\Resolver\SpaceResolverInterface;
use App\Contract\Service\BookingRule\GatekeeperInterface;
use App\Contract\Service\BookingRule\RuleSmithInterface;
use App\Contract\Service\ConductorInterface;
use App\Contract\Service\DoubleBookerBlockerInterface;
use App\Contract\Service\JanitorInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Booking\RecurrenceRule;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\DataObject\Set\OccurrenceSet;
use App\Domain\Exception\RuleTypeMissingImplementationException;
use App\Domain\Exception\SpaceNotFoundException;
use App\Request\BookingRequest;
use Doctrine\ORM\EntityManagerInterface;

final readonly class Conductor implements ConductorInterface
{
    public function __construct(
        private BookingResolverInterface $bookingResolver,
        private BookingPersistorInterface $bookingPersistor,
        private JanitorInterface $janitor,
        private EntityManagerInterface $entityManager,
        private DoubleBookerBlockerInterface $doubleBookerBlocker,
        private GatekeeperInterface $gatekeeper,
        private BookingRuleResolverInterface $ruleResolver,
        private SpaceResolverInterface $spaceResolver,
        private RuleSmithInterface $ruleSmith,
    ) {
    }

    public function upsert(
        BookingRequest $bookingRequest,
        ?int $userId = null,
    ): Booking {
        $bookingRequest->validate();

        $booking = null;
        if ($bookingRequest->getBookingId()) {
            $booking = $this->bookingResolver->resolve(
                id: $bookingRequest->getBookingId(),
            );
        }

        $existingOccurrenceSet = $this->getExistingOccurrences(
            booking: $booking,
        );
        $timeRange = new TimeRange(
            startsAt: $bookingRequest->getStartsAt(),
            endsAt: $bookingRequest->getEndsAt(),
        );
        $recurrenceRule = $this->makeRecurrenceRule(
            request: $bookingRequest,
            booking: $booking,
        );

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

        $this->doubleBookerBlocker->validate(
            booking: $booking,
        );

        $this->validateBookingRules(
            booking: $booking,
        );

        $this->entityManager->beginTransaction();
        try {
            $booking = $this->bookingPersistor->persist(
                booking: $booking,
            );
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
     * @throws RuleTypeMissingImplementationException
     * @throws SpaceNotFoundException
     */
    private function validateBookingRules(
        Booking $booking,
    ): void {
        $space = $this->spaceResolver->resolve(
            id: $booking->getSpaceId(),
        );
        $ruleSet = $this->ruleResolver->resolveManyForWorkspace(
            workspaceId: $space->getWorkspaceId(),
        );
        $rules = $this->ruleSmith->parseRuleSet(
            ruleSet: $ruleSet,
        );

        $this->gatekeeper->validate(
            rules: $rules,
            booking: $booking,
        );
    }

    private function getExistingOccurrences(
        ?Booking $booking,
    ): OccurrenceSet {
        return $booking ? clone $booking->getOccurrences() : new OccurrenceSet();
    }

    private function makeRecurrenceRule(
        BookingRequest $request,
        ?Booking $booking,
    ): RecurrenceRule {
        return new RecurrenceRule(
            rule: $request->getRecurrenceRule(),
            excludedDates: $booking?->getBookingSpec()?->getRecurrenceRule()?->getExcludedDates() ?? [],
        );
    }
}
