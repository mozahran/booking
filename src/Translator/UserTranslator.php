<?php

namespace App\Translator;

use App\Contract\Translator\UserTranslatorInterface;
use App\Domain\DataObject\Set\UserSet;
use App\Domain\DataObject\User;
use App\Domain\Exception\UserNotFoundException;
use App\Entity\UserEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

final readonly class UserTranslator implements UserTranslatorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function toUser(
        UserEntity $entity,
    ): User {
        return new User(
            name: $entity->getName(),
            email: $entity->getEmail(),
            active: $entity->isActive(),
            id: $entity->getId(),
        );
    }

    public function toUserSet(
        array $entities,
    ): UserSet {
        $set = new UserSet();
        foreach ($entities as $entity) {
            $item = $this->toUser(entity: $entity);
            $set->add($item);
        }

        return $set;
    }

    public function toUserEntity(
        User $user,
    ): UserEntity {
        try {
            $entity = match ($user->getId()) {
                null => new UserEntity(),
                default => $this->entityManager->getReference(
                    entityName: UserEntity::class,
                    id: $user->getId(),
                ),
            };
        } catch (ORMException) {
            throw new UserNotFoundException(id: $user->getId());
        }

        $entity->setName($user->getName());
        $entity->setEmail($user->getEmail());
        $entity->setPassword($user->getPassword());
        $entity->setActive($user->isActive());

        return $entity;
    }
}
