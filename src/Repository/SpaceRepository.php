<?php

declare(strict_types=1);

namespace App\Repository;

use App\Contract\Repository\SpaceRepositoryInterface;
use App\Contract\Translator\SpaceTranslatorInterface;
use App\Domain\DataObject\Set\SpaceSet;
use App\Domain\DataObject\Space;
use App\Domain\Exception\SpaceNotFoundException;
use App\Entity\SpaceEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SpaceEntity>
 *
 * @method SpaceEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method SpaceEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method SpaceEntity[]    findAll()
 * @method SpaceEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpaceRepository extends ServiceEntityRepository implements SpaceRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly SpaceTranslatorInterface $spaceTranslator,
    ) {
        parent::__construct($registry, SpaceEntity::class);
    }

    public function findOne(
        int $id,
    ): Space {
        try {
            $spaceEntity = $this
                ->createQueryBuilder('s')
                ->andWhere('s.id = :spaceId')
                ->setParameter('spaceId', $id)
                ->getQuery()
                ->getSingleResult();

            return $this->spaceTranslator->toSpace($spaceEntity);
        } catch (NonUniqueResultException|NoResultException) {
            throw new SpaceNotFoundException();
        }
    }

    public function findMany(
        array $ids,
    ): SpaceSet {
        $entities = $this
            ->createQueryBuilder('s')
            ->andWhere('s.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        return $this->spaceTranslator->toSpaceSet(entities: $entities);
    }

    public function findOneByProvider(
        int $spaceId,
        int $providerId,
    ): Space {
        try {
            $spaceEntity = $this
                ->createQueryBuilder('s')
                ->join('s.workspace', 'w')
                ->join('w.provider', 'p')
                ->andWhere('s.id = :spaceId')
                ->andWhere('w.provider = :providerId')
                ->andWhere('s.active = true')
                ->andWhere('w.active = true')
                ->andWhere('p.active = true')
                ->setParameter('spaceId', $spaceId)
                ->setParameter('providerId', $providerId)
                ->getQuery()
                ->getSingleResult();

            return $this->spaceTranslator->toSpace($spaceEntity);
        } catch (NonUniqueResultException|NoResultException) {
            throw new SpaceNotFoundException();
        }
    }

    public function findManyByWorkspace(
        int $workspaceId,
    ): SpaceSet {
        $entities = $this
            ->createQueryBuilder('s')
            ->andWhere('IDENTITY(s.workspace) = :workspaceId')
            ->setParameter('workspaceId', $workspaceId)
            ->getQuery()
            ->getResult();

        return $this->spaceTranslator->toSpaceSet(entities: $entities);
    }

    public function activate(
        int $id,
    ): void {
        $this
            ->createQueryBuilder('s')
            ->update()
            ->set('s.active', 1)
            ->andWhere('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }

    public function deactivate(
        int $id,
    ): void {
        $this
            ->createQueryBuilder('s')
            ->update()
            ->set('s.active', 0)
            ->andWhere('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }
}
