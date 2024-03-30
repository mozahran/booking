<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use Symfony\Component\HttpFoundation\Response;

class InvalidRecurrenceRuleException extends AppException
{
    protected $message = 'Invalid recurrence rule!';
    protected $code = Response::HTTP_BAD_REQUEST;
}
