<?php

namespace App\Translator;

use App\Contract\Translator\ProviderUserDataTranslatorInterface;
use App\Domain\DataObject\ProviderUserData;
use App\Domain\DataObject\Set\ProviderUserDataSet;
use App\Entity\ProviderUserDataEntity;

class ProviderUserDataTranslator implements ProviderUserDataTranslatorInterface
{
    public function toProviderUserData(
        ProviderUserDataEntity $entity,
    ): ProviderUserData {
        return new ProviderUserData(
            providerId: $entity->getProvider()->getId(),
            userId: $entity->getUser()->getId(),
            role: $entity->getRole(),
            active: $entity->isActive(),
        );
    }

    public function toProviderUserDataSet(
        array $entities,
    ): ProviderUserDataSet {
        $set = new ProviderUserDataSet();
        foreach ($entities as $entity) {
            $providerUserData = $this->toProviderUserData($entity);
            $set->add($providerUserData);
        }

        return $set;
    }
}
