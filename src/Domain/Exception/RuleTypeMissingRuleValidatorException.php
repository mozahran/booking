<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use App\Domain\Enum\RuleType;
use Symfony\Component\HttpFoundation\Response;

class RuleTypeMissingRuleValidatorException extends AppException
{
    protected $code = Response::HTTP_BAD_REQUEST;

    public function __construct(RuleType $ruleType)
    {
        $message = sprintf('Rule type is missing validation class. (Type: %d)', $ruleType->value);

        parent::__construct($message, $this->code);
    }
}
