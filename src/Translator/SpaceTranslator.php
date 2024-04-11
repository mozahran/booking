<?php

namespace App\Translator;

use App\Contract\Translator\SpaceTranslatorInterface;
use App\Domain\DataObject\Set\SpaceSet;
use App\Domain\DataObject\Space;
use App\Domain\Exception\SpaceNotFoundException;
use App\Domain\Exception\WorkspaceNotFoundException;
use App\Entity\SpaceEntity;
use App\Entity\WorkspaceEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

final class SpaceTranslator implements SpaceTranslatorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function toSpace(SpaceEntity $entity): Space
    {
        return new Space(
            name: $entity->getName(),
            active: $entity->isActive(),
            workspaceId: $entity->getWorkspace()->getId(),
            id: $entity->getId(),
        );
    }

    public function toSpaceSet(
        array $entities,
    ): SpaceSet {
        $set = new SpaceSet();
        foreach ($entities as $entity) {
            $space = $this->toSpace(entity: $entity);
            $set->add(item: $space);
        }

        return $set;
    }

    public function toSpaceEntity(
        Space $space,
    ): SpaceEntity {
        try {
            $entity = match ($space->getId()) {
                null => new SpaceEntity(),
                default => $this->entityManager->getReference(
                    entityName: SpaceEntity::class,
                    id: $space->getId(),
                ),
            };
        } catch (ORMException) {
            throw new SpaceNotFoundException(id: $space->getId());
        }

        try {
            /** @var WorkspaceEntity $workspaceEntity */
            $workspaceEntity = $this->entityManager->getReference(
                entityName: WorkspaceEntity::class,
                id: $space->getWorkspaceId(),
            );
        } catch (ORMException) {
            throw new WorkspaceNotFoundException(id: $space->getWorkspaceId());
        }

        $entity->setName($space->getName());
        $entity->setWorkspace($workspaceEntity);
        $entity->setActive($space->isActive());

        return $entity;
    }
}
