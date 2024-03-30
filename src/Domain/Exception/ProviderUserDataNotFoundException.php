<?php

namespace App\Domain\Exception;

use Symfony\Component\HttpFoundation\Response;

class ProviderUserDataNotFoundException extends AppException
{
    protected $message = 'Requested provider user data not found.';
    protected $code = Response::HTTP_BAD_REQUEST;
}
