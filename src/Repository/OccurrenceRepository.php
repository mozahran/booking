<?php

declare(strict_types=1);

namespace App\Repository;

use App\Contract\Repository\OccurrenceRepositoryInterface;
use App\Contract\Translator\OccurrenceTranslatorInterface;
use App\Domain\DataObject\Booking\Occurrence;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\DataObject\Set\OccurrenceSet;
use App\Domain\Exception\OccurrenceNotFoundException;
use App\Entity\OccurrenceEntity;
use App\Utils\TimeDiff;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
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
                ),
            );
            $orX->add(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->gte('o.endsAt', ':'.$startsAtParamName),
                    $queryBuilder->expr()->lte('o.endsAt', ':'.$endsAtParamName),
                ),
            );
            $orX->add(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->lte('o.startsAt', ':'.$startsAtParamName),
                    $queryBuilder->expr()->gte('o.endsAt', ':'.$endsAtParamName),
                ),
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
        if (empty($ids)) {
            return;
        }

        $this
            ->createQueryBuilder('o')
            ->delete()
            ->andWhere('o.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }

    public function getTimeUsageByUserAndSpaceInGivenTimeRanges(
        int $spaceId,
        int $userId,
        array $timeRanges,
    ): int {
        $queryBuilder = $this->createQueryBuilder('o');
        $betweenDates = $this->buildBetweenDates(
            timeRanges: $timeRanges,
            queryBuilder: $queryBuilder,
        );

        $entities = $this->findOccurrenceEntities(
            $queryBuilder,
            $betweenDates,
            $spaceId,
            $userId,
        );

        return $this->aggregateDurations($entities);
    }

    public function getTimeUsageByUserAndSpace(
        int $userId,
        int $spaceId,
    ): int {
        /** @var OccurrenceEntity[] $entities */
        $entities = $this
            ->createQueryBuilder('o')
            ->join('o.booking', 'b')
            ->andWhere('IDENTITY(b.space) = :spaceId')
            ->andWhere('IDENTITY(b.user) = :userId')
            ->andWhere('b.cancelled = 0')
            ->andWhere('o.cancelled = 0')
            ->setParameter('spaceId', $spaceId)
            ->setParameter('userId', $userId);

        return $this->aggregateDurations($entities);
    }

    public function countByUserAndSpaceInGivenTimeRanges(
        int $spaceId,
        int $userId,
        array $timeRanges,
    ): int {
        $queryBuilder = $this->createQueryBuilder('o');
        $betweenDates = $this->buildBetweenDates(
            timeRanges: $timeRanges,
            queryBuilder: $queryBuilder,
        );

        $entities = $this->findOccurrenceEntities(
            $queryBuilder,
            $betweenDates,
            $spaceId,
            $userId,
        );

        return count($entities);
    }

    public function countByUserAndSpace(
        int $userId,
        int $spaceId,
    ): int {
        return (int) $this->createQueryBuilder('o')
            ->select('count(o.id)')
            ->join('o.booking', 'b')
            ->andWhere('IDENTITY(b.space) = :spaceId')
            ->andWhere('IDENTITY(b.user) = :userId')
            ->andWhere('b.cancelled = 0')
            ->andWhere('o.cancelled = 0')
            ->setParameter('spaceId', $spaceId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param TimeRange[] $timeRanges
     */
    private function buildBetweenDates(
        array $timeRanges,
        QueryBuilder $queryBuilder,
    ): Orx {
        $dateConditions = [];
        foreach ($timeRanges as $index => $timeRange) {
            $dateConditions[] = $queryBuilder->expr()->andX(
                $queryBuilder->expr()->gte('o.startsAt', ':startsAt'.$index),
                $queryBuilder->expr()->lte('o.endsAt', ':endsAt'.$index),
            );
            $queryBuilder->setParameter('startsAt'.$index, $timeRange->getStartsAt());
            $queryBuilder->setParameter('endsAt'.$index, $timeRange->getEndsAt());
        }

        return $queryBuilder->expr()->orX(...$dateConditions);
    }

    /**
     * @return OccurrenceEntity[]
     */
    private function findOccurrenceEntities(
        QueryBuilder $queryBuilder,
        Orx $betweenDates,
        int $spaceId,
        int $userId,
    ): array {
        /** @var OccurrenceEntity[] $entities */
        $entities = $queryBuilder
            ->join('o.booking', 'b')
            ->andWhere('IDENTITY(b.space) = :spaceId')
            ->andWhere('IDENTITY(b.user) = :userId')
            ->andWhere('b.cancelled = 0')
            ->andWhere('o.cancelled = 0')
            ->andWhere($betweenDates)
            ->setParameter('spaceId', $spaceId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();

        return $entities;
    }

    private function aggregateDurations(array $entities): int
    {
        $result = 0;
        foreach ($entities as $entity) {
            $result += TimeDiff::minutes(
                first: $entity->getStartsAt(),
                second: $entity->getEndsAt(),
            );
        }

        return $result;
    }
}
