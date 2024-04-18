<?php

namespace App\Resolver;

use App\Contract\Repository\ProviderRepositoryInterface;
use App\Contract\Resolver\ProviderResolverInterface;
use App\Domain\DataObject\Provider;
use App\Domain\DataObject\Set\ProviderSet;

final readonly class ProviderResolver implements ProviderResolverInterface
{
    public function __construct(
        private ProviderRepositoryInterface $providerRepository,
    ) {
    }

    public function resolve(
        int $id,
    ): Provider {
        return $this->providerRepository->findOne(id: $id);
    }

    public function resolveMany(
        array $ids,
    ): ProviderSet {
        return $this->providerRepository->findMany(ids: $ids);
    }

    public function resolveAll(): ProviderSet
    {
        return $this->providerRepository->all();
    }
}
