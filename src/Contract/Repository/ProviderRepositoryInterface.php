<?php

declare(strict_types=1);

namespace App\Contract\Repository;

use App\Domain\DataObject\Provider;
use App\Domain\DataObject\Set\ProviderSet;
use App\Domain\Exception\ProviderNotFoundException;

interface ProviderRepositoryInterface
{
    /**
     * @throws ProviderNotFoundException
     */
    public function findOne(
        int $id,
    ): Provider;

    public function findMany(
        array $ids,
    ): ProviderSet;

    public function all(): ProviderSet;

    public function activate(
        int $id,
    ): void;

    public function deactivate(
        int $id,
    ): void;
}
