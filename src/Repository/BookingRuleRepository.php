<?php

namespace App\Repository;

use App\Contract\Repository\BookingRuleRepositoryInterface;
use App\Contract\Translator\BookingRuleTranslatorInterface;
use App\Domain\DataObject\BookingRule;
use App\Domain\DataObject\Set\BookingRuleSet;
use App\Domain\Exception\BookingRuleNotFoundException;
use App\Entity\BookingRuleEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BookingRuleEntity>
 *
 * @method BookingRuleEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method BookingRuleEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method BookingRuleEntity[]    findAll()
 * @method BookingRuleEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookingRuleRepository extends ServiceEntityRepository implements BookingRuleRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly BookingRuleTranslatorInterface $ruleTranslator,
    ) {
        parent::__construct($registry, BookingRuleEntity::class);
    }

    public function findOne(
        int $id,
    ): BookingRule {
        try {
            $entity = $this
                ->createQueryBuilder('o')
                ->andWhere('o.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getSingleResult();

            return $this->ruleTranslator->toBookingRule(entity: $entity);
        } catch (NonUniqueResultException|NoResultException) {
            throw new BookingRuleNotFoundException(id: $id);
        }
    }

    public function findManyByWorkspace(
        int $workspaceId,
    ): BookingRuleSet {
        $entities = $this
            ->createQueryBuilder('r')
            ->andWhere('r.workspace = :workspaceId')
            ->andWhere('r.active = true')
            ->setParameter('workspaceId', $workspaceId)
            ->getQuery()
            ->getResult();

        return $this->ruleTranslator->toBookingRuleSet(entities: $entities);
    }

    public function activate(
        int $id,
    ): void {
        $this
            ->createQueryBuilder('br')
            ->update()
            ->set('br.active', 1)
            ->andWhere('br.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }

    public function deactivate(
        int $id,
    ): void {
        $this
            ->createQueryBuilder('br')
            ->update()
            ->set('br.active', 0)
            ->andWhere('br.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }
}
