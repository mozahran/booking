<?php

declare(strict_types=1);

namespace App\Contract\Translator;

use App\Domain\DataObject\Provider;
use App\Domain\DataObject\Set\ProviderSet;
use App\Domain\Exception\ProviderNotFoundException;
use App\Entity\ProviderEntity;

interface ProviderTranslatorInterface
{
    public function toProvider(
        ProviderEntity $entity,
    ): Provider;

    /**
     * @param ProviderEntity[] $entities
     */
    public function toProviderSet(
        array $entities,
    ): ProviderSet;

    /**
     * @throws ProviderNotFoundException
     */
    public function toProviderEntity(
        Provider $provider,
    ): ProviderEntity;
}
