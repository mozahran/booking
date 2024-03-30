<?php

declare(strict_types=1);

namespace App\Contract\Resolver;

use App\Domain\DataObject\Set\WorkspaceSet;
use App\Domain\DataObject\Workspace;
use App\Domain\Exception\WorkspaceNotFoundException;

interface WorkspaceResolverInterface
{
    /**
     * @throws WorkspaceNotFoundException
     */
    public function resolve(
        int $id,
    ): Workspace;

    /**
     * @param int[] $ids
     */
    public function resolveMany(
        array $ids,
    ): WorkspaceSet;

    public function resolveByProvider(
        int $providerId,
    ): WorkspaceSet;
}
