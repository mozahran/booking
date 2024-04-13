<?php

declare(strict_types=1);

namespace App\Validator;

use App\Contract\DataObject\RuleInterface;
use App\Contract\Resolver\UserResolverInterface;
use App\Contract\Validator\RuleValidatorInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Rule\Window;
use App\Domain\Enum\Rule\Predicate;
use App\Domain\Exception\RuleViolationException;
use App\Domain\Exception\UserNotFoundException;
use App\Utils\TimeDiff;

final class WindowRuleValidator implements RuleValidatorInterface
{
    private const MINUTES_IN_DAY = 1440;

    public function __construct(
        private readonly UserResolverInterface $userResolver,
    ) {
    }

    /**
     * @throws UserNotFoundException
     */
    public function validate(
        Booking $booking,
        Window|RuleInterface $rule,
    ): array {
        if ($this->shouldIgnoreValidation($rule, $booking)) {
            return [];
        }

        try {
            match ($rule->getPredicate()) {
                Predicate::LESS_THAN => $this->validateLessThan($rule, $booking),
                Predicate::MORE_THAN_STRICT => $this->validateMoreThanStrict($rule, $booking),
                Predicate::MORE_THAN_INCLUDING_TODAY => $this->validateMoreThanIncludingToday($rule, $booking),
            };
        } catch (RuleViolationException $exception) {
            return [$exception];
        }

        return [];
    }

    /**
     * @throws UserNotFoundException
     */
    private function shouldIgnoreValidation(
        Window $rule,
        Booking $booking,
    ): bool {
        return $this->shouldBeIgnoredIfBookingSpaceIsNotTargeted($rule, $booking)
            || $this->shouldBeIgnoredIfBookingUserIsNotTargeted($rule, $booking);
    }

    private function shouldBeIgnoredIfBookingSpaceIsNotTargeted(
        Window $rule,
        Booking $booking,
    ): bool {
        $spaceIds = $rule->getSpaceIds();

        return is_array($spaceIds) && !in_array($booking->getSpaceId(), $spaceIds);
    }

    /**
     * @throws UserNotFoundException
     */
    private function shouldBeIgnoredIfBookingUserIsNotTargeted(
        Window $rule,
        Booking $booking,
    ): bool {
        if (false === is_array($rule->getRoles())) {
            return false;
        }

        $user = $this->userResolver->resolve(id: $booking->getUserId());

        return 0 === count(array_intersect($rule->getRoles(), $user->getRoles()));
    }

    /**
     * @throws RuleViolationException
     */
    private function validateLessThan(Window $rule, Booking $booking): void
    {
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
    private function validateMoreThanStrict(Window $rule, Booking $booking): void
    {
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
    private function validateMoreThanIncludingToday(Window $rule, Booking $booking): void
    {
        $value = $rule->getValue() * $rule->getPredicate()->coefficient();
        $diffInMinutes = TimeDiff::minutes($booking->getTimeRange()->getStartsAt());
        if ($diffInMinutes < $value) {
            return;
        }

        throw RuleViolationException::windowMoreThanIncludingToday($value);
    }
}
