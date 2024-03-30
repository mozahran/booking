<?php

declare(strict_types=1);

namespace App\Repository;

use App\Contract\Repository\WorkspaceRepositoryInterface;
use App\Contract\Translator\WorkspaceTranslatorInterface;
use App\Domain\DataObject\Set\WorkspaceSet;
use App\Domain\DataObject\Workspace;
use App\Domain\Exception\WorkspaceNotFoundException;
use App\Entity\WorkspaceEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorkspaceEntity>
 *
 * @method WorkspaceEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorkspaceEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorkspaceEntity[]    findAll()
 * @method WorkspaceEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkspaceRepository extends ServiceEntityRepository implements WorkspaceRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly WorkspaceTranslatorInterface $workspaceTranslator,
    ) {
        parent::__construct($registry, WorkspaceEntity::class);
    }

    public function findOne(
        int $id,
    ): Workspace {
        try {
            $entity = $this
                ->createQueryBuilder('w')
                ->andWhere('w.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getSingleResult();

            return $this->workspaceTranslator->toWorkspace($entity);
        } catch (NonUniqueResultException|NoResultException) {
            throw new WorkspaceNotFoundException();
        }
    }

    public function findMany(
        array $ids,
    ): WorkspaceSet {
        $entities = $this
            ->createQueryBuilder('w')
            ->andWhere('w.id IN(:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        return $this->workspaceTranslator->toWorkspaceSet($entities);
    }

    public function findManyByProvider(
        int $providerId,
    ): WorkspaceSet {
        $entities = $this
            ->createQueryBuilder('w')
            ->andWhere('IDENTITY(w.provider) = :providerId')
            ->setParameter('providerId', $providerId)
            ->getQuery()
            ->getResult();

        return $this->workspaceTranslator->toWorkspaceSet($entities);
    }

    public function activate(
        int $id,
    ): void {
        $this
            ->createQueryBuilder('w')
            ->update()
            ->set('w.active', 1)
            ->andWhere('w.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }

    public function deactivate(
        int $id,
    ): void {
        $this
            ->createQueryBuilder('w')
            ->update()
            ->set('w.active', 0)
            ->andWhere('w.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }
}
