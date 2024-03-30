<?php

namespace App\Contract\Repository;

use App\Domain\DataObject\Set\WorkspaceSet;
use App\Domain\DataObject\Workspace;
use App\Domain\Exception\WorkspaceNotFoundException;

interface WorkspaceRepositoryInterface
{
    /**
     * @throws WorkspaceNotFoundException
     */
    public function findOne(
        int $id,
    ): Workspace;

    public function findMany(
        array $ids,
    ): WorkspaceSet;

    public function findManyByProvider(
        int $providerId,
    ): WorkspaceSet;

    public function activate(
        int $id,
    ): void;

    public function deactivate(
        int $id,
    ): void;
}
