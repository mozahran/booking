<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use Symfony\Component\HttpFoundation\Response;

class InvalidTimeRangeException extends AppException
{
    protected $message = 'Invalid time range!';
    protected $code = Response::HTTP_BAD_REQUEST;
}
