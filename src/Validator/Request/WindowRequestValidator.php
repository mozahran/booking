<?php

namespace App\Validator\Request;

use App\Domain\Enum\Rule\Predicate;
use App\Domain\Exception\RequestValidationException;

class WindowRequestValidator
{
    private array $errors = [];

    public function requirePredicate(?int $value): self
    {
        if (null === Predicate::tryFrom($value)) {
            $this->errors[] = 'Parameter "predicate" is required and must be either 1, 2 or 3';
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
