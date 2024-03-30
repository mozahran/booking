<?php

declare(strict_types=1);

namespace App\Contract\Repository;

use App\Domain\DataObject\ProviderUserData;
use App\Domain\DataObject\Set\ProviderUserDataSet;
use App\Domain\Exception\ProviderUserDataNotFoundException;

interface ProviderUserDataRepositoryInterface
{
    /**
     * @throws ProviderUserDataNotFoundException
     */
    public function findOne(
        int $userId,
        int $providerId,
    ): ProviderUserData;

    public function findManyByUser(
        int $userId,
    ): ProviderUserDataSet;
}
