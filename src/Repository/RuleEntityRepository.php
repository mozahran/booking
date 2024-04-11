<?php

namespace App\Repository;

use App\Entity\RuleEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RuleEntity>
 *
 * @method RuleEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method RuleEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method RuleEntity[]    findAll()
 * @method RuleEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RuleEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RuleEntity::class);
    }

    //    /**
    //     * @return RuleEntity[] Returns an array of RuleEntity objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?RuleEntity
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
