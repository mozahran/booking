<?php

declare(strict_types=1);

namespace App\Validator\Rule;

use App\Contract\DataObject\RuleInterface;
use App\Contract\Resolver\UserResolverInterface;
use App\Contract\Validator\RuleValidatorInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Rule\Window;
use App\Domain\Enum\Rule\Predicate;
use App\Domain\Exception\RuleViolationException;
use App\Domain\Exception\UserNotFoundException;
use App\Utils\RuleViolationList;
use App\Utils\TimeDiff;

final readonly class WindowRuleValidator implements RuleValidatorInterface
{
    public function __construct(
        private UserResolverInterface $userResolver,
    ) {
    }

    public function validate(
        Booking $booking,
        Window|RuleInterface $rule,
    ): RuleViolationList {
        $ruleViolationList = RuleViolationList::create();
        if ($this->isNotApplicableToBookingUser(rule: $rule, booking: $booking)) {
            return $ruleViolationList;
        }

        try {
            match ($rule->getPredicate()) {
                Predicate::LESS_THAN => $this->validateLessThan($rule, $booking),
                Predicate::MORE_THAN_STRICT => $this->validateMoreThanStrict($rule, $booking),
                Predicate::MORE_THAN_INCLUDING_TODAY => $this->validateMoreThanIncludingToday($rule, $booking),
            };
        } catch (RuleViolationException $exception) {
            $ruleViolationList->add($exception);
        }

        return $ruleViolationList;
    }

    private function isNotApplicableToBookingUser(
        Window $rule,
        Booking $booking,
    ): bool {
        if (null === $rule->getRoles()) {
            return false;
        }

        try {
            $user = $this->userResolver->resolve(id: $booking->getUserId());
        } catch (UserNotFoundException) {
            return false;
        }

        return 0 === count(array_intersect($rule->getRoles(), $user->getRoles()));
    }

    /**
     * @throws RuleViolationException
     */
    private function validateLessThan(
        Window $rule,
        Booking $booking,
    ): void {
        $value = $rule->getValue() * $rule->getPredicate()->coefficient();
        $diffInMinutes = TimeDiff::minutes($booking->getTimeRange()->getStartsAt());
        if ($diffInMinutes >= $value) {
            return;
        }

        throw RuleViolationException::windowLessThan($value);
    }

    /**
     * @throws RuleViolationException
     */
    private function validateMoreThanStrict(
        Window $rule,
        Booking $booking,
    ): void {
        $value = $rule->getValue() * $rule->getPredicate()->coefficient();
        $diffInMinutes = TimeDiff::minutes($booking->getTimeRange()->getStartsAt());
        if ($diffInMinutes > $value) {
            return;
        }

        throw RuleViolationException::windowMoreThanStrict($value);
    }

    /**
     * @throws RuleViolationException
     */
    private function validateMoreThanIncludingToday(
        Window $rule,
        Booking $booking,
    ): void {
        $value = $rule->getValue() * $rule->getPredicate()->coefficient();
        $diffInMinutes = TimeDiff::minutes($booking->getTimeRange()->getStartsAt());
        if ($diffInMinutes < $value) {
            return;
        }

        throw RuleViolationException::windowMoreThanIncludingToday($value);
    }
}
