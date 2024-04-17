<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\DataObject\RuleInterface;
use App\Contract\Service\GatekeeperInterface;
use App\Contract\Validator\RuleValidatorInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\Exception\RuleTypeMissingRuleValidatorException;
use App\Domain\Exception\RuleViolationException;
use App\Utils\RuleViolationList;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class Gatekeeper implements GatekeeperInterface
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
     * @throws RuleTypeMissingRuleValidatorException
     * @throws RuleViolationException
     */
    public function validate(
        array $rules,
        Booking $booking,
    ): void {
        $violations = RuleViolationList::create();
        foreach ($rules as $rule) {
            if ($this->isNotApplicableToBooking(rule: $rule, booking: $booking)) {
                continue;
            }
            foreach ($this->ruleValidators as $ruleValidator) {
                if (get_class($ruleValidator) === $rule->getType()->ruleValidator()) {
                    $ruleViolationList = $ruleValidator->validate($booking, $rule);
                    $violations->merge(
                        violations: $ruleViolationList->all(),
                    );
                }
            }
        }
        if (false === $violations->isEmpty()) {
            throw $violations->asSingleException();
        }
    }

    private function isNotApplicableToBooking(
        RuleInterface $rule,
        Booking $booking,
    ): bool {
        if (null === $rule->getSpaceIds()) {
            return false;
        }

        return false === in_array($booking->getSpaceId(), $rule->getSpaceIds());
    }
}
