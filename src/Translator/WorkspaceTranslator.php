<?php

declare(strict_types=1);

namespace App\Translator;

use App\Contract\Translator\WorkspaceTranslatorInterface;
use App\Domain\DataObject\Set\WorkspaceSet;
use App\Domain\DataObject\Workspace;
use App\Domain\Exception\ProviderNotFoundException;
use App\Domain\Exception\WorkspaceNotFoundException;
use App\Entity\ProviderEntity;
use App\Entity\WorkspaceEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

final readonly class WorkspaceTranslator implements WorkspaceTranslatorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function toWorkspace(
        WorkspaceEntity $entity,
    ): Workspace {
        return new Workspace(
            name: $entity->getName(),
            active: $entity->isActive(),
            providerId: $entity->getProvider()->getId(),
            id: $entity->getId(),
        );
    }

    public function toWorkspaceSet(
        array $entities,
    ): WorkspaceSet {
        $set = new WorkspaceSet();
        foreach ($entities as $entity) {
            $workspace = $this->toWorkspace(entity: $entity);
            $set->add($workspace);
        }

        return $set;
    }

    public function toWorkspaceEntity(
        Workspace $workspace,
    ): WorkspaceEntity {
        try {
            $entity = match ($workspace->getId()) {
                null => new WorkspaceEntity(),
                default => $this->entityManager->getReference(
                    entityName: WorkspaceEntity::class,
                    id: $workspace->getId(),
                ),
            };
        } catch (ORMException) {
            throw new WorkspaceNotFoundException(id: $workspace->getProviderId());
        }

        try {
            /** @var ProviderEntity $providerEntity */
            $providerEntity = $this->entityManager->getReference(
                entityName: ProviderEntity::class,
                id: $workspace->getProviderId(),
            );
        } catch (ORMException) {
            throw new ProviderNotFoundException(id: $workspace->getProviderId());
        }

        $entity->setName($workspace->getName());
        $entity->setProvider($providerEntity);
        $entity->setActive($workspace->isActive());

        return $entity;
    }
}
