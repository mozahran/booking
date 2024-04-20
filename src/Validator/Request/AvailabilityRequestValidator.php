<?php

namespace App\Validator\Request;

use App\Domain\Exception\RequestValidationException;

class AvailabilityRequestValidator
{
    private array $errors = [];

    public function requireDaysBitmask(?int $value): self
    {
        if (!is_int($value) && 0 !== $value) {
            $this->errors[] = 'Parameter daysBitmask is required and must be an integer';
        }

        return $this;
    }

    public function requireStartMinutes(?int $value): self
    {
        if (!is_int($value) && 0 !== $value) {
            $this->errors[] = 'Parameter startMinutes is required and must be an integer';
        }

        return $this;
    }

    public function requireEndMinutes(?int $value): self
    {
        if (!is_int($value) && 0 !== $value) {
            $this->errors[] = 'Parameter endMinutes is required and must be an integer';
        }

        return $this;
    }

    public function maybeSpaceIds(?array $value): self
    {
        if (null === $value
            || count($value) > 0) {
            return $this;
        }

        $this->errors[] = 'Parameter spaceIds must be null or an array of integers';

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
