<?php

declare(strict_types=1);

namespace App\Repository;

use App\Contract\Repository\BookingRepositoryInterface;
use App\Contract\Translator\BookingTranslatorInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\DataObject\Set\BookingSet;
use App\Domain\DataObject\Set\TimeRangeSet;
use App\Domain\Exception\BookingNotFoundException;
use App\Entity\BookingEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BookingEntity>
 *
 * @method BookingEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method BookingEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method BookingEntity[]    findAll()
 * @method BookingEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookingRepository extends ServiceEntityRepository implements BookingRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly BookingTranslatorInterface $bookingTranslator,
    ) {
        parent::__construct($registry, BookingEntity::class);
    }

    public function findOne(
        int $id,
    ): Booking {
        try {
            $entity = $this
                ->createQueryBuilder('b')
                ->select('b', 'o')
                ->leftJoin('b.occurrences', 'o')
                ->andWhere('b.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getSingleResult();

            return $this->bookingTranslator->toBooking($entity);
        } catch (NonUniqueResultException|NoResultException) {
            throw new BookingNotFoundException();
        }
    }

    public function findMany(
        array $ids,
    ): BookingSet {
        $entities = $this
            ->createQueryBuilder('b')
            ->andWhere('b.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        return $this->bookingTranslator->toBookingSet($entities);
    }

    public function findManyByRange(
        int $spaceId,
        TimeRange $timeRange,
    ): BookingSet {
        $entities = $this
            ->createQueryBuilder('b')
            ->leftJoin('b.occurrences', 'o')
            ->andWhere('IDENTITY(b.space) = :spaceId')
            ->andWhere('o.startsAt >= :startsAt')
            ->andWhere('o.endsAt <= :endsAt')
            ->setParameter('startsAt', $timeRange->getStartsAt())
            ->setParameter('endsAt', $timeRange->getEndsAt())
            ->setParameter('spaceId', $spaceId)
            ->getQuery()
            ->getResult();

        return $this->bookingTranslator->toBookingSet($entities);
    }

    public function cancel(
        array $ids,
        int $cancellerId,
    ): void {
        $this
            ->createQueryBuilder('b')
            ->update()
            ->set('b.cancelled', 1)
            ->set('b.canceller', $cancellerId)
            ->andWhere('b.cancelled != 1')
            ->andWhere('b.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }

    public function countBufferConflicts(
        int $spaceId,
        TimeRangeSet $timeRangeSet,
    ): int {
        if ($timeRangeSet->isEmpty()) {
            return 0;
        }
        $queryBuilder = $this
            ->createQueryBuilder('b')
            ->select('count(b)')
            ->leftJoin('b.occurrences', 'o')
            ->andWhere('IDENTITY(b.space) = :spaceId')
            ->setParameter('spaceId', $spaceId);

        $expressions = [];
        $timeRanges = $timeRangeSet->items();
        foreach ($timeRanges as $timeRange) {
            $and = $queryBuilder->expr()->orX();
            $and->add(
                arg: $queryBuilder->expr()->gt(
                    x: 'o.startsAt',
                    y: $queryBuilder->expr()->literal(
                        $timeRange->getEndsAt()->format(TimeRange::DATETIME_FORMAT),
                    ),
                ),
            );
            $and->add(
                arg: $queryBuilder->expr()->gt(
                    x: 'o.endsAt',
                    y: $queryBuilder->expr()->literal(
                        $timeRange->getStartsAt()->format(TimeRange::DATETIME_FORMAT),
                    ),
                ),
            );
            $expressions[] = $and;
        }

        $and = $queryBuilder->expr()->andX();
        foreach ($expressions as $expression) {
            $and->add($expression);
        }
        $queryBuilder->andWhere($and);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function findByTimeRangeForUser(
        TimeRange $timeRange,
        int $userId,
    ): BookingSet {
        $entities = $this
            ->createQueryBuilder('b')
            ->join('b.occurrences', 'o')
            ->andWhere('b.user = :user')
            ->andWhere('o.startsAt >= :startsAt')
            ->andWhere('o.endsAt <= :endsAt')
            ->andWhere('b.cancelled = false')
            ->andWhere('o.cancelled = false')
            ->setParameter('user', $userId)
            ->setParameter('startsAt', $timeRange->getStartsAt())
            ->setParameter('endsAt', $timeRange->getEndsAt())
            ->getQuery()
            ->getResult();

        return $this->bookingTranslator->toBookingSet($entities);
    }
}
