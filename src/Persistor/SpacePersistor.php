<?php

namespace App\Persistor;

use App\Contract\Persistor\SpacePersistorInterface;
use App\Contract\Translator\SpaceTranslatorInterface;
use App\Domain\DataObject\Space;
use App\Domain\Exception\SpaceNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

final readonly class SpacePersistor implements SpacePersistorInterface
{
    public function __construct(
        private SpaceTranslatorInterface $spaceTranslator,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function persist(
        Space $space,
    ): Space {
        $entity = $this->spaceTranslator->toSpaceEntity($space);
        if (!$entity->getId()) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();
        try {
            $this->entityManager->refresh($entity);
        } catch (ORMException) {
            throw new SpaceNotFoundException($entity->getId());
        }

        return $this->spaceTranslator->toSpace($entity);
    }
}
