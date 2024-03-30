<?php

declare(strict_types=1);

namespace App\Repository;

use App\Contract\Repository\OccurrenceRepositoryInterface;
use App\Contract\Translator\OccurrenceTranslatorInterface;
use App\Domain\DataObject\Booking\Occurrence;
use App\Domain\DataObject\Set\OccurrenceSet;
use App\Domain\Exception\OccurrenceNotFoundException;
use App\Entity\OccurrenceEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OccurrenceEntity>
 *
 * @method OccurrenceEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method OccurrenceEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method OccurrenceEntity[]    findAll()
 * @method OccurrenceEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OccurrenceRepository extends ServiceEntityRepository implements OccurrenceRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly OccurrenceTranslatorInterface $occurrenceTranslator,
    ) {
        parent::__construct($registry, OccurrenceEntity::class);
    }

    public function findOne(
        int $id,
    ): Occurrence {
        try {
            $entity = $this
                ->createQueryBuilder('o')
                ->andWhere('o.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getSingleResult();

            return $this->occurrenceTranslator->toOccurrence(entity: $entity);
        } catch (NonUniqueResultException|NoResultException) {
            throw new OccurrenceNotFoundException();
        }
    }

    public function findMany(
        array $ids,
    ): OccurrenceSet {
        $entities = $this
            ->createQueryBuilder('o')
            ->andWhere('o.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        return $this->occurrenceTranslator->toOccurrenceSet(entities: $entities);
    }

    public function findManyByBooking(
        int $bookingId,
    ): OccurrenceSet {
        $entities = $this
            ->createQueryBuilder('o')
            ->andWhere('IDENTITY(o.booking) = :bookingId')
            ->setParameter('bookingId', $bookingId)
            ->getQuery()
            ->getResult();

        return $this->occurrenceTranslator->toOccurrenceSet(entities: $entities);
    }

    public function findForConflictDetection(
        int $spaceId,
        OccurrenceSet $occurrenceSet,
        ?int $id,
    ): OccurrenceSet {
        $queryBuilder = $this
            ->createQueryBuilder('o')
            ->join('o.booking', 'b')
            ->andWhere('b.space = :spaceId')
            ->andWhere('o.cancelled = 0')
            ->setParameter('spaceId', $spaceId);

        $orX = $queryBuilder->expr()->orX();

        $occurrenceSetItems = $occurrenceSet->items();
        foreach ($occurrenceSetItems as $i => $occurrence) {
            $startsAtParamName = 'startAt'.$i;
            $endsAtParamName = 'endsAt'.$i;
            $orX->add(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->gte('o.startsAt', ':'.$startsAtParamName),
                    $queryBuilder->expr()->lte('o.startsAt', ':'.$endsAtParamName),
                )
            );
            $orX->add(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->gte('o.endsAt', ':'.$startsAtParamName),
                    $queryBuilder->expr()->lte('o.endsAt', ':'.$endsAtParamName),
                )
            );
            $orX->add(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->lte('o.startsAt', ':'.$startsAtParamName),
                    $queryBuilder->expr()->gte('o.endsAt', ':'.$endsAtParamName),
                )
            );
            $timeRange = clone $occurrence->getTimeRange();
            $startsAt = $timeRange->getStartsAt()->modify('+1 second');
            $endsAt = $timeRange->getEndsAt()->modify('-1 second');
            $queryBuilder
                ->setParameter($startsAtParamName, $startsAt)
                ->setParameter($endsAtParamName, $endsAt);
        }

        $queryBuilder->andWhere($orX);

        if (null !== $id) {
            $queryBuilder
                ->andWhere('b.id != :excludedBookingId')
                ->setParameter('excludedBookingId', $id);
        }

        $occurrences = $queryBuilder
            ->getQuery()
            ->getResult();

        return $this->occurrenceTranslator->toOccurrenceSet($occurrences);
    }

    public function cancel(
        array $ids,
        int $cancellerId,
    ): void {
        $this
            ->createQueryBuilder('o')
            ->update()
            ->set('o.cancelled', 1)
            ->set('o.canceller', $cancellerId)
            ->andWhere('o.cancelled != 1')
            ->andWhere('o.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }

    public function cancelSelectedAndFollowing(
        int $id,
        int $bookingId,
        int $cancellerId,
    ): void {
        $this
            ->createQueryBuilder('o')
            ->update()
            ->set('o.cancelled', 1)
            ->set('o.canceller', $cancellerId)
            ->andWhere('o.cancelled != 1')
            ->andWhere('o.id >= :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }

    public function delete(
        array $ids,
    ): void {
        $this
            ->createQueryBuilder('o')
            ->delete()
            ->andWhere('o.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }
}
