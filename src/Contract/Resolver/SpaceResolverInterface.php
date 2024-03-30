<?php

declare(strict_types=1);

namespace App\Contract\Resolver;

use App\Domain\DataObject\Set\SpaceSet;
use App\Domain\DataObject\Space;
use App\Domain\Exception\SpaceNotFoundException;

interface SpaceResolverInterface
{
    /**
     * @throws SpaceNotFoundException
     */
    public function resolve(
        int $id,
    ): Space;

    public function resolveMany(
        array $ids,
    ): SpaceSet;

    public function resolveByWorkspace(
        int $workspaceId,
    ): SpaceSet;
}
