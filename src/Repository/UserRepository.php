<?php

declare(strict_types=1);

namespace App\Repository;

use App\Contract\Repository\UserRepositoryInterface;
use App\Contract\Translator\UserTranslatorInterface;
use App\Domain\DataObject\Set\UserSet;
use App\Domain\DataObject\User;
use App\Domain\Exception\UserNotFoundException;
use App\Entity\UserEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<UserEntity>
 *
 * @method UserEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserEntity[]    findAll()
 * @method UserEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface, UserRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly UserTranslatorInterface $userTranslator,
    ) {
        parent::__construct($registry, UserEntity::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof UserEntity) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findOne(
        int $id,
    ): User {
        try {
            $entity = $this
                ->createQueryBuilder('s')
                ->andWhere('s.id = :spaceId')
                ->setParameter('spaceId', $id)
                ->getQuery()
                ->getSingleResult();

            return $this->userTranslator->toUser($entity);
        } catch (NonUniqueResultException|NoResultException) {
            throw new UserNotFoundException(id: $id);
        }
    }

    public function findMany(): UserSet
    {
        $entities = $this
            ->createQueryBuilder('u')
            ->getQuery()
            ->getResult();

        return $this->userTranslator->toUserSet($entities);
    }

    public function activate(
        int $id,
    ): void {
        $this
            ->createQueryBuilder('u')
            ->update()
            ->set('u.active', 1)
            ->andWhere('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }

    public function deactivate(
        int $id,
    ): void {
        $this
            ->createQueryBuilder('u')
            ->update()
            ->set('u.active', 0)
            ->andWhere('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }
}
