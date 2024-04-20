<?php

namespace App\Translator;

use App\Contract\Translator\BookingRuleTranslatorInterface;
use App\Domain\DataObject\BookingRule;
use App\Domain\DataObject\Set\BookingRuleSet;
use App\Domain\Exception\BookingRuleNotFoundException;
use App\Domain\Exception\WorkspaceNotFoundException;
use App\Entity\BookingRuleEntity;
use App\Entity\WorkspaceEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

final readonly class BookingRuleTranslator implements BookingRuleTranslatorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function toBookingRule(
        BookingRuleEntity $entity,
    ): BookingRule {
        return new BookingRule(
            workspaceId: $entity->getWorkspace()->getId(),
            name: $entity->getName(),
            type: $entity->getType(),
            content: $entity->getContent(),
            active: $entity->isActive(),
            id: $entity->getId(),
        );
    }

    /**
     * @throws WorkspaceNotFoundException
     * @throws BookingRuleNotFoundException
     */
    public function toBookingRuleEntity(
        BookingRule $bookingRule,
    ): BookingRuleEntity {
        $workspaceId = $bookingRule->getWorkspaceId();
        try {
            $entity = match ($bookingRule->getId()) {
                null => new BookingRuleEntity(),
                default => $this->entityManager->getReference(
                    entityName: BookingRuleEntity::class,
                    id: $bookingRule->getId(),
                ),
            };
        } catch (ORMException) {
            throw new BookingRuleNotFoundException(id: $bookingRule->getId());
        }

        try {
            /** @var WorkspaceEntity $workspaceEntity */
            $workspaceEntity = $this->entityManager->getReference(
                entityName: WorkspaceEntity::class,
                id: $bookingRule->getWorkspaceId(),
            );
        } catch (ORMException) {
            throw new WorkspaceNotFoundException(id: $workspaceId);
        }

        $entity->setName($bookingRule->getName());
        $entity->setType($bookingRule->getType());
        $entity->setActive($bookingRule->isActive());
        $entity->setContent($bookingRule->getContent());
        $entity->setWorkspace($workspaceEntity);

        return $entity;
    }

    public function toBookingRuleSet(
        array $entities,
    ): BookingRuleSet {
        $ruleSet = new BookingRuleSet();
        foreach ($entities as $entity) {
            $rule = $this->toBookingRule(entity: $entity);
            $ruleSet->add($rule);
        }

        return $ruleSet;
    }
}
