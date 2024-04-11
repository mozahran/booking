<?php

namespace App\Resolver;

use App\Contract\Repository\SpaceRepositoryInterface;
use App\Contract\Resolver\SpaceResolverInterface;
use App\Domain\DataObject\Set\SpaceSet;
use App\Domain\DataObject\Space;

final class SpaceResolver implements SpaceResolverInterface
{
    public function __construct(
        private SpaceRepositoryInterface $spaceRepository,
    ) {
    }

    public function resolve(
        int $id,
    ): Space {
        return $this->spaceRepository->findOne(id: $id);
    }

    public function resolveMany(
        array $ids,
    ): SpaceSet {
        return $this->spaceRepository->findMany(ids: $ids);
    }

    public function resolveByWorkspace(
        int $workspaceId,
    ): SpaceSet {
        return $this->spaceRepository->findManyByWorkspace(workspaceId: $workspaceId);
    }
}
