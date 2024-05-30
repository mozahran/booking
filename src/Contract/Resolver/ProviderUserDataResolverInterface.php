<?php

declare(strict_types=1);

namespace App\Contract\Resolver;

use App\Domain\DataObject\Set\ProviderUserDataSet;

interface ProviderUserDataResolverInterface
{
    /**
     * @param int[] $userIds
     */
    public function resolveByUsers(
        array $userIds,
    ): ProviderUserDataSet;
}
