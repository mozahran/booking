<?php

namespace App\Validator\Request;

use App\Domain\Enum\Rule\AggregationMetric;
use App\Domain\Enum\Rule\Mode;
use App\Domain\Enum\Rule\Period;
use App\Domain\Exception\RequestValidationException;

class QuotaRequestValidator
{
    private array $errors = [];

    public function requireDaysBitmask(?int $value): self
    {
        if (!is_int($value) && 0 !== $value) {
            $this->errors[] = 'Parameter "daysBitmask" is required and must be an integer';
        }

        return $this;
    }

    public function requireStartMinutes(?int $value): self
    {
        if (!is_int($value) && 0 !== $value) {
            $this->errors[] = 'Parameter "startMinutes" is required and must be an integer';
        }

        return $this;
    }

    public function requireEndMinutes(?int $value): self
    {
        if (!is_int($value) && 0 !== $value) {
            $this->errors[] = 'Parameter "endMinutes" is required and must be an integer';
        }

        return $this;
    }

    public function requireValue(?int $value): self
    {
        if (!is_int($value) && 0 !== $value) {
            $this->errors[] = 'Parameter "value" is required and must be an integer';
        }

        return $this;
    }

    public function requireAggregationMetric(?int $value): self
    {
        if (null === AggregationMetric::tryFrom($value)) {
            $this->errors[] = 'Parameter "aggregationMetric" is required and must be either 1 or 2';
        }

        return $this;
    }

    public function requireMode(?int $value): self
    {
        if (null === Mode::tryFrom($value)) {
            $this->errors[] = 'Parameter "mode" is required and must be either 0, 1 or 2';
        }

        return $this;
    }

    public function requirePeriod(?int $value): self
    {
        if (null === Period::tryFrom($value)) {
            $this->errors[] = 'Parameter "value" is required and must be either 1, 2, 3 or 2';
        }

        return $this;
    }

    public function maybeRoles(?array $value): self
    {
        if (null === $value
            || count($value) > 0) {
            return $this;
        }

        $this->errors[] = 'Parameter "roles" must be null or an array of strings';

        return $this;
    }

    public function maybeSpaceIds(?array $value): self
    {
        if (null === $value
            || count($value) > 0) {
            return $this;
        }

        $this->errors[] = 'Parameter "spaceIds" must be null or an array of integers';

        return $this;
    }

    /**
     * @throws RequestValidationException
     */
    public function validate(): void
    {
        if (empty($this->errors)) {
            return;
        }

        throw new RequestValidationException($this->errors);
    }
}
