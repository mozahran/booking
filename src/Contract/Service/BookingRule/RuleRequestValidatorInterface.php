<?php

declare(strict_types=1);

namespace App\Contract\Service\BookingRule;

use App\Domain\Exception\AppException;
use App\Domain\Exception\RequestValidationException;

interface RuleRequestValidatorInterface
{
    /**
     * @throws RequestValidationException
     * @throws AppException
     */
    public function validate(
        string $rule,
        string $type,
    ): void;
}
