<?php

declare(strict_types=1);

namespace App\Domain\Enum;

enum WorkspaceType: string
{
    case INTERNAL = 'internal';
    case EXTERNAL = 'external';
}
