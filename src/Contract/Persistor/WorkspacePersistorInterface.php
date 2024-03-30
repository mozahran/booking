<?php

declare(strict_types=1);

namespace App\Contract\Persistor;

use App\Domain\DataObject\Workspace;
use App\Domain\Exception\ProviderNotFoundException;
use App\Domain\Exception\WorkspaceNotFoundException;

interface WorkspacePersistorInterface
{
    /**
     * @throws WorkspaceNotFoundException
     * @throws ProviderNotFoundException
     */
    public function persist(
        Workspace $workspace,
    ): Workspace;
}
