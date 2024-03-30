<?php

namespace App\Contract\Translator;

use App\Domain\DataObject\ProviderUserData;
use App\Domain\DataObject\Set\ProviderUserDataSet;
use App\Entity\ProviderUserDataEntity;

interface ProviderUserDataTranslatorInterface
{
    public function toProviderUserData(
        ProviderUserDataEntity $entity,
    ): ProviderUserData;

    /**
     * @param ProviderUserDataEntity[] $entities
     */
    public function toProviderUserDataSet(
        array $entities,
    ): ProviderUserDataSet;
}
