<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use Symfony\Component\HttpFoundation\Response;

class BookingNotFoundException extends AppException
{
    protected $message = 'Requested booking not found.';
    protected $code = Response::HTTP_BAD_REQUEST;
}
