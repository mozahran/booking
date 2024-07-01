<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Set;

use App\Domain\DataObject\ProviderUserData;

/**
 * @method ProviderUserData|null        first()
 * @method ProviderUserData|null        last()
 * @method ProviderUserData[]           items()
 * @method array<int, ProviderUserData> indexByUserId()
 * @method add(ProviderUserData $item)
 * @method remove(ProviderUserData $item)
 */
class ProviderUserDataSet extends AbstractSet
{
    /**
     * @return ProviderUserData[]
     */
    public function indexByUserId(): array
    {
        $providerUserData = $this->items();
        $result = [];
        foreach ($providerUserData as $providerUserDatum) {
            $userId = $providerUserDatum->getUserId();
            $result[$userId][] = $providerUserDatum;
        }

        return $result;
    }
}
