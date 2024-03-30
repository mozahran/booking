<?php

declare(strict_types=1);

namespace App\Contract\Persistor;

use App\Domain\DataObject\Space;
use App\Domain\Exception\SpaceNotFoundException;
use App\Domain\Exception\WorkspaceNotFoundException;

interface SpacePersistorInterface
{
    /**
     * @throws SpaceNotFoundException
     * @throws WorkspaceNotFoundException
     */
    public function persist(
        Space $space,
    ): Space;
}
