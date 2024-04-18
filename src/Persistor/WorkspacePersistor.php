<?php

declare(strict_types=1);

namespace App\Persistor;

use App\Contract\Persistor\WorkspacePersistorInterface;
use App\Contract\Translator\WorkspaceTranslatorInterface;
use App\Domain\DataObject\Workspace;
use App\Domain\Exception\WorkspaceNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

final readonly class WorkspacePersistor implements WorkspacePersistorInterface
{
    public function __construct(
        private WorkspaceTranslatorInterface $workspaceTranslator,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function persist(
        Workspace $workspace,
    ): Workspace {
        $entity = $this->workspaceTranslator->toWorkspaceEntity($workspace);
        if (!$entity->getId()) {
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
        try {
            $this->entityManager->refresh($entity);
        } catch (ORMException) {
            throw new WorkspaceNotFoundException($entity->getId());
        }

        return $this->workspaceTranslator->toWorkspace($entity);
    }
}
