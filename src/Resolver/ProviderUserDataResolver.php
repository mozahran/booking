<?php

declare(strict_types=1);

namespace App\Resolver;

use App\Contract\Repository\ProviderUserDataRepositoryInterface;
use App\Contract\Resolver\ProviderUserDataResolverInterface;
use App\Domain\DataObject\Set\ProviderUserDataSet;

final readonly class ProviderUserDataResolver implements ProviderUserDataResolverInterface
{
    public function __construct(
        private ProviderUserDataRepositoryInterface $providerUserDataRepository,
    ) {
    }

    public function resolveByUsers(
        array $userIds,
    ): ProviderUserDataSet {
        return $this->providerUserDataRepository->findManyByUsers(
            userIds: $userIds,
        );
    }
}
