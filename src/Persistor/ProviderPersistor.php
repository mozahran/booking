<?php

namespace App\Persistor;

use App\Contract\Persistor\ProviderPersistorInterface;
use App\Contract\Translator\ProviderTranslatorInterface;
use App\Domain\DataObject\Provider;
use App\Domain\Exception\ProviderNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

final readonly class ProviderPersistor implements ProviderPersistorInterface
{
    public function __construct(
        private ProviderTranslatorInterface $providerTranslator,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function persist(
        Provider $provider,
    ): Provider {
        $entity = $this->providerTranslator->toProviderEntity($provider);
        if (!$entity->getId()) {
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
        try {
            $this->entityManager->refresh($entity);
        } catch (ORMException) {
            throw new ProviderNotFoundException($entity->getId());
        }

        return $this->providerTranslator->toProvider($entity);
    }
}
