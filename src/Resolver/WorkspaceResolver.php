<?php

namespace App\Resolver;

use App\Contract\Repository\WorkspaceRepositoryInterface;
use App\Contract\Resolver\WorkspaceResolverInterface;
use App\Domain\DataObject\Set\WorkspaceSet;
use App\Domain\DataObject\Workspace;

final class WorkspaceResolver implements WorkspaceResolverInterface
{
    public function __construct(
        private WorkspaceRepositoryInterface $workspaceRepository,
    ) {
    }

    public function resolve(
        int $id,
    ): Workspace {
        return $this->workspaceRepository->findOne(id: $id);
    }

    public function resolveMany(
        array $ids,
    ): WorkspaceSet {
        return $this->workspaceRepository->findMany(ids: $ids);
    }

    public function resolveByProvider(
        int $providerId,
    ): WorkspaceSet {
        return $this->workspaceRepository->findManyByProvider(
            providerId: $providerId,
        );
    }
}
