<?php

declare(strict_types=1);

namespace App\Repository;

use App\Contract\Repository\ProviderRepositoryInterface;
use App\Contract\Translator\ProviderTranslatorInterface;
use App\Domain\DataObject\Provider;
use App\Domain\DataObject\Set\ProviderSet;
use App\Domain\Exception\ProviderNotFoundException;
use App\Entity\ProviderEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Provider>
 *
 * @method ProviderEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProviderEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProviderEntity[]    findAll()
 * @method ProviderEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProviderRepository extends ServiceEntityRepository implements ProviderRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly ProviderTranslatorInterface $providerTranslator,
    ) {
        parent::__construct($registry, ProviderEntity::class);
    }

    public function findOne(
        int $id,
    ): Provider {
        try {
            $entity = $this
                ->createQueryBuilder('p')
                ->andWhere('p.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getSingleResult();

            return $this->providerTranslator->toProvider(entity: $entity);
        } catch (NonUniqueResultException|NoResultException) {
            throw new ProviderNotFoundException(id: $id);
        }
    }

    public function findMany(
        array $ids,
    ): ProviderSet {
        $entities = $this
            ->createQueryBuilder('p')
            ->andWhere('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        return $this->providerTranslator->toProviderSet(entities: $entities);
    }

    public function findManyByUser(
        int $userId,
    ): ProviderSet {
        $entities = $this
            ->createQueryBuilder('p')
            ->andWhere('p.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();

        return $this->providerTranslator->toProviderSet(entities: $entities);
    }

    public function all(): ProviderSet
    {
        $entities = $this
            ->createQueryBuilder('p')
            ->getQuery()
            ->getResult();

        return $this->providerTranslator->toProviderSet(entities: $entities);
    }

    public function activate(
        int $id,
    ): void {
        $this
            ->createQueryBuilder('p')
            ->update()
            ->set('p.active', 1)
            ->andWhere('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }

    public function deactivate(
        int $id,
    ): void {
        $this
            ->createQueryBuilder('p')
            ->update()
            ->set('p.active', 0)
            ->andWhere('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }
}
