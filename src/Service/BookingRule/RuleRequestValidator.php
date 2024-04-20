<?php

declare(strict_types=1);

namespace App\Service\BookingRule;

use App\Contract\Service\BookingRule\RuleRequestValidatorInterface;
use App\Domain\Enum\RuleType;
use App\Domain\Exception\AppException;
use App\Domain\Exception\RequestValidationException;
use App\Validator\Request\AvailabilityRequestValidator;
use App\Validator\Request\BufferRequestValidator;
use App\Validator\Request\DenyRequestValidator;
use App\Validator\Request\QuotaRequestValidator;
use App\Validator\Request\WindowRequestValidator;

class RuleRequestValidator implements RuleRequestValidatorInterface
{
    /**
     * @throws RequestValidationException
     * @throws AppException
     */
    public function validate(
        string $rule,
        string $type,
    ): void {
        $rule = json_decode(
            json: $rule,
            associative: true,
        );
        match ($type) {
            RuleType::AVAILABILITY->value => $this->validateAvailabilityRule($rule),
            RuleType::BUFFER->value => $this->validateBufferRule($rule),
            RuleType::DENY->value => $this->validateDenyRule($rule),
            RuleType::QUOTA->value => $this->validateQuotaRule($rule),
            RuleType::WINDOW->value => $this->validateWindowRule($rule),
            default => throw new AppException('Unexpected rule type'),
        };
    }

    /**
     * @throws RequestValidationException
     */
    private function validateAvailabilityRule(array $rule): void
    {
        (new AvailabilityRequestValidator())
            ->requireDaysBitmask($rule['daysBitmask'] ?? null)
            ->requireStartMinutes($rule['startMinutes'] ?? null)
            ->requireEndMinutes($rule['endMinutes'] ?? null)
            ->maybeSpaceIds($rule['spaceIds'] ?? null)
            ->validate();
    }

    /**
     * @throws RequestValidationException
     */
    private function validateBufferRule(array $rule): void
    {
        (new BufferRequestValidator())
            ->requireValue($rule['value'] ?? null)
            ->maybeSpaceIds($rule['spaceIds'] ?? null)
            ->validate();
    }

    /**
     * @throws RequestValidationException
     */
    private function validateDenyRule(array $rule): void
    {
        (new DenyRequestValidator())
            ->requireDaysBitmask($rule['daysBitmask'] ?? null)
            ->requireStartMinutes($rule['startMinutes'] ?? null)
            ->requireEndMinutes($rule['endMinutes'] ?? null)
            ->requireConditionGroups($rule['conditionGroups'] ?? null)
            ->maybeSpaceIds($rule['spaceIds'] ?? null)
            ->validate();
    }

    /**
     * @throws RequestValidationException
     */
    private function validateQuotaRule(array $rule): void
    {
        (new QuotaRequestValidator())
            ->requireDaysBitmask($rule['daysBitmask'] ?? null)
            ->requireStartMinutes($rule['startMinutes'] ?? null)
            ->requireEndMinutes($rule['endMinutes'] ?? null)
            ->requireAggregationMetric($rule['aggregationMode'] ?? null)
            ->requireMode($rule['mode'] ?? null)
            ->requirePeriod($rule['period'] ?? null)
            ->maybeRoles($rule['roles'] ?? null)
            ->maybeSpaceIds($rule['spaceIds'] ?? null)
            ->validate();
    }

    /**
     * @throws RequestValidationException
     */
    private function validateWindowRule(array $rule): void
    {
        (new WindowRequestValidator())
            ->requireValue($rule['value'] ?? null)
            ->requirePredicate($rule['predicate'] ?? null)
            ->maybeRoles($rule['roles'] ?? null)
            ->maybeSpaceIds($rule['spaceIds'] ?? null)
            ->validate();
    }
}
