<?php

declare(strict_types=1);

namespace App\Validator\Rule;

use App\Contract\DataObject\RuleInterface;
use App\Contract\Resolver\UserResolverInterface;
use App\Contract\Service\TimeWardenInterface;
use App\Contract\Validator\RuleValidatorInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Rule\Condition;
use App\Domain\DataObject\Rule\Deny;
use App\Domain\Enum\Rule\Operand;
use App\Domain\Enum\Rule\Predicate;
use App\Domain\Exception\RuleViolationException;
use App\Domain\Exception\UserNotFoundException;
use App\Utils\Comparator;
use App\Utils\RuleViolationList;

final readonly class DenyRuleValidator implements RuleValidatorInterface
{
    public function __construct(
        private UserResolverInterface $userResolver,
        private TimeWardenInterface $timeWarden,
    ) {
    }

    public function validate(
        Booking $booking,
        Deny|RuleInterface $rule,
    ): RuleViolationList {
        $ruleViolationList = RuleViolationList::create();

        $timeBoundaryViolations = $this->timeWarden->validateBoundaries(
            booking: $booking,
            rule: $rule,
        );

        $ruleViolationList->merge($timeBoundaryViolations->all());

        $conditionGroups = $rule->getConditionGroups();
        foreach ($conditionGroups as $conditionGroup) {
            $conditions = $conditionGroup->getConditions();
            foreach ($conditions as $condition) {
                try {
                    match ($condition->getOperand()) {
                        Operand::ITS_DURATION => $this->validateDuration($booking, $condition),
                        Operand::INTERVAL_FROM_MIDNIGHT => $this->validateIntervalFromMidnight($booking, $condition),
                        Operand::INTERVAL_TO_MIDNIGHT => $this->validateIntervalToMidnight($booking, $condition),
                        Operand::USER_ROLES => $this->validateUserRoles($booking, $condition),
                    };
                } catch (RuleViolationException $exception) {
                    $ruleViolationList->add($exception);
                }
            }
        }

        return $ruleViolationList;
    }

    /**
     * @throws RuleViolationException
     */
    private function validateDuration(
        Booking $booking,
        Condition $condition,
    ): void {
        $value = $condition->getValue();
        $duration = $booking->getTimeRange()->getDuration();
        $operator = $condition->getOperator();

        if (Comparator::is(x: $duration, operator: $operator, y: $value)) {
            throw RuleViolationException::durationIs($operator, $value);
        }
    }

    /**
     * @throws RuleViolationException
     */
    private function validateIntervalFromMidnight(
        Booking $booking,
        Condition $condition,
    ): void {
        $midnightInMinutes = 0;
        $value = $condition->getValue();
        $operator = $condition->getOperator();
        $bookingStartTime = $booking->getTimeRange()->getStartMinutes();
        $diffFromMidnight = abs($midnightInMinutes - $bookingStartTime);

        if (Comparator::is(x: $diffFromMidnight, operator: $operator, y: $value)) {
            throw RuleViolationException::intervalFromMidnight($operator, $value);
        }
    }

    /**
     * @throws RuleViolationException
     */
    private function validateIntervalToMidnight(
        Booking $booking,
        Condition $condition,
    ): void {
        $value = $condition->getValue();
        $operator = $condition->getOperator();
        $bookingStartMinutes = $booking->getTimeRange()->getStartMinutes();

        $diffToMidnight = abs(Predicate::MINUTES_IN_DAY - $bookingStartMinutes);
        if (Comparator::is(x: $diffToMidnight, operator: $operator, y: $value)) {
            throw RuleViolationException::intervalToMidnight($operator, $value);
        }
    }

    /**
     * @throws RuleViolationException
     */
    private function validateUserRoles(
        Booking $booking,
        Condition $condition,
    ): void {
        try {
            $user = $this->userResolver->resolve(id: $booking->getUserId());
        } catch (UserNotFoundException) {
            return;
        }
        $value = $condition->getValue();
        $operator = $condition->getOperator();

        if (Comparator::is(x: $user->getRoles(), operator: $operator, y: $value)) {
            throw RuleViolationException::userRoles();
        }
    }
}
