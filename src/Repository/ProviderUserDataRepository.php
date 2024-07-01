<?php

declare(strict_types=1);

namespace App\Repository;

use App\Contract\Repository\ProviderUserDataRepositoryInterface;
use App\Contract\Translator\ProviderUserDataTranslatorInterface;
use App\Domain\DataObject\ProviderUserData;
use App\Domain\DataObject\Set\ProviderUserDataSet;
use App\Domain\Exception\ProviderUserDataNotFoundException;
use App\Entity\ProviderUserDataEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProviderUserDataEntity>
 *
 * @method ProviderUserDataEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProviderUserDataEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProviderUserDataEntity[]    findAll()
 * @method ProviderUserDataEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProviderUserDataRepository extends ServiceEntityRepository implements ProviderUserDataRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly ProviderUserDataTranslatorInterface $providerUserDataTranslator,
    ) {
        parent::__construct($registry, ProviderUserDataEntity::class);
    }

    public function findOne(
        int $userId,
        int $providerId,
    ): ProviderUserData {
        try {
            $entity = $this
                ->createQueryBuilder('pud')
                ->andWhere('pud.user = :userId')
                ->andWhere('pud.provider = :providerId')
                ->andWhere('pud.active = true')
                ->setParameter('userId', $userId)
                ->setParameter('providerId', $providerId)
                ->getQuery()
                ->getSingleResult();

            return $this->providerUserDataTranslator->toProviderUserData($entity);
        } catch (NoResultException|NonUniqueResultException) {
            throw new ProviderUserDataNotFoundException();
        }
    }

    public function findManyByUser(
        int $userId,
    ): ProviderUserDataSet {
        $entities = $this
            ->createQueryBuilder('pud')
            ->andWhere('pud.user = :userId')
            ->andWhere('pud.active = true')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();

        return $this->providerUserDataTranslator->toProviderUserDataSet($entities);
    }

    public function findManyByUsers(
        array $userIds,
    ): ProviderUserDataSet {
        $entities = $this
            ->createQueryBuilder('pud')
            ->andWhere('pud.user IN (:userIds)')
            ->andWhere('pud.active = true')
            ->setParameter('userIds', $userIds)
            ->getQuery()
            ->getResult();

        return $this->providerUserDataTranslator->toProviderUserDataSet($entities);
    }
}
