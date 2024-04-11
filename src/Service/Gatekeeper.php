<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\DataObject\RuleInterface;
use App\Contract\Service\GatekeeperInterface;
use App\Contract\Validator\RuleValidatorInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\Exception\RuleTypeMissingRuleValidatorException;
use App\Domain\Exception\RuleViolationException;
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
     *
     * @throws RuleTypeMissingRuleValidatorException
     * @throws RuleViolationException
     */
    public function validate(
        array $rules,
        Booking $booking,
    ): void {
        $ruleViolationExceptions = [];
        foreach ($rules as $rule) {
            array_push($ruleViolationExceptions, ...$this->applyRuleToBooking($rule, $booking));
        }
        $this->complainUnlessEmpty($ruleViolationExceptions);
    }

    /**
     * @throws RuleTypeMissingRuleValidatorException
     */
    private function applyRuleToBooking(
        RuleInterface $rule,
        Booking $booking,
    ): array {
        foreach ($this->ruleValidators as $ruleValidator) {
            if (get_class($ruleValidator) === $rule->getType()->ruleValidator()) {
                return $ruleValidator->validate($booking, $rule);
            }
        }

        return [];
    }

    /**
     * @param RuleViolationException[] $ruleViolationExceptions
     *
     * @throws RuleViolationException
     */
    private function complainUnlessEmpty(array $ruleViolationExceptions): void
    {
        if (empty($ruleViolationExceptions)) {
            return;
        }

        $violationMessages = [];
        foreach ($ruleViolationExceptions as $violation) {
            $violationMessages[] = $violation->getMessage();
        }
        throw new RuleViolationException(message: implode(PHP_EOL, $violationMessages));
    }
}
