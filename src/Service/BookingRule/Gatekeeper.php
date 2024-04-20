<?php

declare(strict_types=1);

namespace App\Service\BookingRule;

use App\Contract\DataObject\RuleInterface;
use App\Contract\Service\BookingRule\GatekeeperInterface;
use App\Contract\Validator\RuleValidatorInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\Exception\RuleViolationException;
use App\Utils\RuleViolationList;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

final readonly class Gatekeeper implements GatekeeperInterface
{
    /**
     * @param RuleValidatorInterface[] $ruleValidators
     */
    public function __construct(
        #[TaggedIterator(tag: RuleValidatorInterface::TAG)]
        private iterable $ruleValidators,
    ) {
    }

    /**
     * @param RuleInterface[] $rules
     *
     * @throws RuleViolationException
     */
    public function validate(
        array $rules,
        Booking $booking,
    ): void {
        $violations = RuleViolationList::empty();
        foreach ($rules as $rule) {
            $isRuleApplicableToBooking = $this->isRuleApplicableToBooking(
                rule: $rule,
                booking: $booking,
            );
            if (false === $isRuleApplicableToBooking) {
                continue;
            }
            $ruleViolationList = $this->applyRuleToBooking(
                rule: $rule,
                booking: $booking,
            );
            $violations->merge(
                violations: $ruleViolationList->all(),
            );
        }
        if ($violations->isEmpty()) {
            return;
        }
        throw $violations->asSingleException();
    }

    private function isRuleApplicableToBooking(
        RuleInterface $rule,
        Booking $booking,
    ): bool {
        if (null === $rule->getSpaceIds()) {
            return true;
        }

        return in_array(
            needle: $booking->getSpaceId(),
            haystack: $rule->getSpaceIds(),
        );
    }

    private function applyRuleToBooking(
        RuleInterface $rule,
        Booking $booking,
    ): RuleViolationList {
        $ruleViolationList = RuleViolationList::empty();
        foreach ($this->ruleValidators as $ruleValidator) {
            $isRuleValidatorMatching = $this->isRuleValidatorMatching(
                ruleValidator: $ruleValidator,
                rule: $rule,
            );
            if (false === $isRuleValidatorMatching) {
                continue;
            }
            $ruleViolationList = $ruleValidator->validate(
                booking: $booking,
                rule: $rule,
            );
        }

        return $ruleViolationList;
    }

    private function isRuleValidatorMatching(
        RuleValidatorInterface $ruleValidator,
        RuleInterface $rule,
    ): bool {
        return get_class($ruleValidator) === $rule->getType()->validator();
    }
}
