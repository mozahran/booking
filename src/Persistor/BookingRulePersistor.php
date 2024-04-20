<?php

declare(strict_types=1);

namespace App\Persistor;

use App\Contract\Persistor\BookingRulePersistorInterface;
use App\Contract\Resolver\BookingRuleResolverInterface;
use App\Contract\Translator\BookingRuleTranslatorInterface;
use App\Domain\DataObject\BookingRule;
use App\Domain\Exception\BookingRuleNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

final readonly class BookingRulePersistor implements BookingRulePersistorInterface
{
    public function __construct(
        private BookingRuleTranslatorInterface $bookingRuleTranslator,
        private BookingRuleResolverInterface $bookingRuleResolver,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function persist(
        BookingRule $rule,
    ): BookingRule {
        $entity = $this->bookingRuleTranslator->toBookingRuleEntity(bookingRule: $rule);
        if (!$entity->getId()) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();
        try {
            $this->entityManager->refresh($entity);
        } catch (ORMException) {
            throw new BookingRuleNotFoundException($rule->getId());
        }

        return $this->bookingRuleResolver->resolve(id: $entity->getId());
    }
}
