<?php

namespace App\Translator;

use App\Contract\Translator\ProviderTranslatorInterface;
use App\Domain\DataObject\Provider;
use App\Domain\DataObject\Set\ProviderSet;
use App\Domain\Exception\ProviderNotFoundException;
use App\Domain\Exception\UserNotFoundException;
use App\Entity\ProviderEntity;
use App\Entity\UserEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

final readonly class ProviderTranslator implements ProviderTranslatorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function toProvider(
        ProviderEntity $entity,
    ): Provider {
        return new Provider(
            name: $entity->getName(),
            active: $entity->isActive(),
            userId: $entity->getUser()->getId(),
            id: $entity->getId(),
        );
    }

    public function toProviderSet(
        array $entities,
    ): ProviderSet {
        $set = new ProviderSet();
        foreach ($entities as $entity) {
            $providerUserData = $this->toProvider(entity: $entity);
            $set->add(item: $providerUserData);
        }

        return $set;
    }

    public function toProviderEntity(
        Provider $provider,
    ): ProviderEntity {
        try {
            $entity = match ($provider->getId()) {
                null => new ProviderEntity(),
                default => $this->entityManager->getReference(
                    entityName: ProviderEntity::class,
                    id: $provider->getId(),
                ),
            };
        } catch (ORMException) {
            throw new ProviderNotFoundException(id: $provider->getId());
        }

        try {
            /** @var UserEntity $userEntity */
            $userEntity = $this->entityManager->getReference(
                entityName: UserEntity::class,
                id: $provider->getUserId(),
            );
        } catch (ORMException) {
            throw new UserNotFoundException(id: $provider->getUserId());
        }

        $entity->setName($provider->getName());
        $entity->setUser($userEntity);
        $entity->setActive($provider->isActive());

        return $entity;
    }
}
