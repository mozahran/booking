<?php

namespace App\Translator;

use App\Builder\BookingBuilder;
use App\Contract\Translator\BookingTranslatorInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Booking\Occurrence;
use App\Domain\DataObject\Booking\RecurrenceRule;
use App\Domain\DataObject\Booking\Status;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\DataObject\Set\BookingSet;
use App\Domain\DataObject\Set\OccurrenceSet;
use App\Domain\Exception\BookingNotFoundException;
use App\Domain\Exception\SpaceNotFoundException;
use App\Domain\Exception\UserNotFoundException;
use App\Entity\BookingEntity;
use App\Entity\OccurrenceEntity;
use App\Entity\SpaceEntity;
use App\Entity\UserEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

final class BookingTranslator implements BookingTranslatorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function toBooking(
        BookingEntity $entity,
    ): Booking {
        $timeRange = new TimeRange(
            startsAt: $entity->getStartsAt()->format(\DateTimeInterface::ATOM),
            endsAt: $entity->getEndsAt()->format(\DateTimeInterface::ATOM),
        );
        $recurrenceRule = new RecurrenceRule(
            rule: $entity->getRecurrenceRule(),
            excludedDates: $entity->getExcludedDates(),
        );

        $builder = new BookingBuilder();
        $builder->setId($entity->getId());
        $builder->setSpaceId($entity->getSpace()->getId());
        $builder->setUserId($entity->getUser()->getId());
        $builder->setTimeRange($timeRange);
        $builder->setRecurrenceRule($recurrenceRule);
        if ($entity->isCancelled()) {
            $builder->setCancelled($entity->getCanceller()?->getId());
        }

        $existingOccurrences = $this->toOccurrenceSet($entity->getOccurrences()->toArray());
        $builder->setOccurrenceSet($existingOccurrences);

        return $builder->build();
    }

    public function toBookingSet(
        array $entities,
    ): BookingSet {
        $set = new BookingSet();
        foreach ($entities as $entity) {
            $booking = $this->toBooking($entity);
            $set->add($booking);
        }

        return $set;
    }

    public function toOccurrence(
        OccurrenceEntity $entity,
    ): Occurrence {
        $startsAt = $entity->getStartsAt()->format(\DateTimeInterface::ATOM);
        $endsAt = $entity->getEndsAt()->format(\DateTimeInterface::ATOM);

        return new Occurrence(
            timeRange: new TimeRange(startsAt: $startsAt, endsAt: $endsAt),
            status: new Status($entity->isCancelled(), $entity->getCanceller()?->getId()),
            bookingId: $entity->getBooking()->getId(),
            id: $entity->getId(),
        );
    }

    public function toOccurrenceSet(
        array $entities,
    ): OccurrenceSet {
        $set = new OccurrenceSet();
        foreach ($entities as $entity) {
            $occurrence = $this->toOccurrence($entity);
            $set->add($occurrence);
        }

        return $set;
    }

    public function toBookingEntity(
        Booking $booking,
    ): BookingEntity {
        try {
            $bookingEntity = match ($booking->getId()) {
                null => new BookingEntity(),
                default => $this->entityManager->getReference(
                    entityName: BookingEntity::class,
                    id: $booking->getId(),
                ),
            };
        } catch (ORMException) {
            throw new BookingNotFoundException(id: $booking->getId());
        }

        try {
            /** @var SpaceEntity $spaceEntity */
            $spaceEntity = $this->entityManager->getReference(
                entityName: SpaceEntity::class,
                id: $booking->getSpaceId(),
            );
        } catch (ORMException) {
            throw new SpaceNotFoundException(id: $booking->getSpaceId());
        }

        try {
            /** @var UserEntity $userEntity */
            $userEntity = $this->entityManager->getReference(
                entityName: UserEntity::class,
                id: $booking->getUserId(),
            );
        } catch (ORMException) {
            throw new UserNotFoundException(id: $booking->getUserId());
        }

        $timeRange = $booking->getTimeRange();

        $bookingEntity->setUser($userEntity);
        $bookingEntity->setSpace($spaceEntity);
        $bookingEntity->setStartsAt($timeRange->getStartsAt());
        $bookingEntity->setEndsAt($timeRange->getEndsAt());
        $bookingEntity->setRecurrenceRule($booking->getBookingSpec()->getRecurrenceRule()?->getRule());

        return $bookingEntity;
    }
}
