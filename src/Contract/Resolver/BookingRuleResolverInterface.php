<?php

declare(strict_types=1);

namespace App\Contract\Resolver;

use App\Domain\DataObject\BookingRule;
use App\Domain\DataObject\Set\BookingRuleSet;

interface BookingRuleResolverInterface
{
    public function resolve(
        int $id,
    ): BookingRule;

    public function resolveManyForWorkspace(
        int $workspaceId,
    ): BookingRuleSet;
}
