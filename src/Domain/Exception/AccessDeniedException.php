<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use Symfony\Component\HttpFoundation\Response;

class AccessDeniedException extends AppException
{
    protected $message = 'Access Denied.';
    protected $code = Response::HTTP_FORBIDDEN;
}
