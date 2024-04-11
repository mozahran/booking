<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use App\Domain\Enum\RuleType;
use Symfony\Component\HttpFoundation\Response;

class RuleTypeMissingImplemenationException extends AppException
{
    protected $code = Response::HTTP_BAD_REQUEST;

    public function __construct(RuleType $ruleType)
    {
        $message = sprintf('Requested rule is missing implementation. (Type: %d)', $ruleType->value);

        parent::__construct($message, $this->code);
    }
}
