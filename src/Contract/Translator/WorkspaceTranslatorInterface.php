<?php

declare(strict_types=1);

namespace App\Contract\Translator;

use App\Domain\DataObject\Set\WorkspaceSet;
use App\Domain\DataObject\Workspace;
use App\Domain\Exception\ProviderNotFoundException;
use App\Domain\Exception\WorkspaceNotFoundException;
use App\Entity\WorkspaceEntity;

interface WorkspaceTranslatorInterface
{
    public function toWorkspace(
        WorkspaceEntity $entity,
    ): Workspace;

    /**
     * @param WorkspaceEntity[] $entities
     */
    public function toWorkspaceSet(
        array $entities,
    ): WorkspaceSet;

    /**
     * @throws WorkspaceNotFoundException
     * @throws ProviderNotFoundException
     */
    public function toWorkspaceEntity(
        Workspace $workspace,
    ): WorkspaceEntity;
}
