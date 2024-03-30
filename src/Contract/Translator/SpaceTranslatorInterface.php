<?php

namespace App\Contract\Translator;

use App\Domain\DataObject\Set\SpaceSet;
use App\Domain\DataObject\Space;
use App\Domain\Exception\SpaceNotFoundException;
use App\Domain\Exception\WorkspaceNotFoundException;
use App\Entity\SpaceEntity;

interface SpaceTranslatorInterface
{
    public function toSpace(
        SpaceEntity $entity,
    ): Space;

    /**
     * @param SpaceEntity[] $entities
     */
    public function toSpaceSet(
        array $entities,
    ): SpaceSet;

    /**
     * @throws WorkspaceNotFoundException
     * @throws SpaceNotFoundException
     */
    public function toSpaceEntity(
        Space $space,
    ): SpaceEntity;
}
