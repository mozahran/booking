<?php

namespace App\Contract\Repository;

use App\Domain\DataObject\Set\SpaceSet;
use App\Domain\DataObject\Space;
use App\Domain\Exception\SpaceNotFoundException;

interface SpaceRepositoryInterface
{
    /**
     * @throws SpaceNotFoundException
     */
    public function findOne(
        int $id,
    ): Space;

    /**
     * @param int[] $ids
     */
    public function findMany(
        array $ids,
    ): SpaceSet;

    /**
     * @throws SpaceNotFoundException
     */
    public function findOneByProvider(
        int $spaceId,
        int $providerId,
    ): Space;

    public function findManyByWorkspace(
        int $workspaceId,
    ): SpaceSet;

    public function activate(
        int $id,
    ): void;

    public function deactivate(
        int $id,
    ): void;
}
