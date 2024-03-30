<?php

declare(strict_types=1);

namespace App\Contract\Resolver;

use App\Domain\DataObject\Provider;
use App\Domain\DataObject\Set\ProviderSet;
use App\Domain\Exception\ProviderNotFoundException;

interface ProviderResolverInterface
{
    /**
     * @throws ProviderNotFoundException
     */
    public function resolve(
        int $id,
    ): Provider;

    public function resolveMany(
        array $ids,
    ): ProviderSet;

    public function resolveAll(): ProviderSet;
}
