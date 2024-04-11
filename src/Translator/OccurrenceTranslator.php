<?php

declare(strict_types=1);

namespace App\Translator;

use App\Contract\Translator\OccurrenceTranslatorInterface;
use App\Domain\DataObject\Booking\Occurrence;
use App\Domain\DataObject\Booking\Status;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\DataObject\Set\OccurrenceSet;
use App\Domain\Exception\OccurrenceNotFoundException;
use App\Entity\OccurrenceEntity;
use App\Entity\UserEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

final class OccurrenceTranslator implements OccurrenceTranslatorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function toOccurrence(
        OccurrenceEntity $entity,
    ): Occurrence {
        $timeRange = new TimeRange(
            startsAt: $entity->getStartsAt()->format(\DateTimeInterface::ATOM),
            endsAt: $entity->getEndsAt()->format(\DateTimeInterface::ATOM),
        );
        $status = new Status(
            cancelled: $entity->isCancelled(),
            cancellerId: $entity->getCanceller()?->getId(),
        );

        return new Occurrence(
            timeRange: $timeRange,
            status: $status,
            bookingId: $entity->getBooking()->getId(),
            id: $entity->getId(),
        );
    }

    public function toOccurrenceSet(
        array $entities,
    ): OccurrenceSet {
        $set = new OccurrenceSet();
        foreach ($entities as $entity) {
            $occurrence = $this->toOccurrence(entity: $entity);
            $set->add($occurrence);
        }

        return $set;
    }

    public function toOccurrenceEntity(
        Occurrence $occurrence,
    ): OccurrenceEntity {
        try {
            $occurrenceEntity = match ($occurrence->getId()) {
                null => new OccurrenceEntity(),
                default => $this->entityManager->getReference(
                    entityName: OccurrenceEntity::class,
                    id: $occurrence->getId(),
                ),
            };
        } catch (ORMException) {
            throw new OccurrenceNotFoundException(id: $occurrence->getId());
        }

        $cancellerEntity = null;
        try {
            if ($occurrence->getCancellerId()) {
                /** @var UserEntity $cancellerEntity */
                $cancellerEntity = $this->entityManager->getReference(
                    entityName: UserEntity::class,
                    id: $occurrence->getCancellerId(),
                );
            }
        } catch (ORMException) {
        }

        $timeRange = $occurrence->getTimeRange();

        $occurrenceEntity->setStartsAt($timeRange->getStartsAt());
        $occurrenceEntity->setEndsAt($timeRange->getEndsAt());
        $occurrenceEntity->setCancelled($occurrence->isCancelled());
        $occurrenceEntity->setCanceller($cancellerEntity);

        return $occurrenceEntity;
    }
}
