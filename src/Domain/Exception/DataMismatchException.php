<?php

namespace App\Domain\Exception;

use Symfony\Component\HttpFoundation\Response;

class DataMismatchException extends AppException
{
    protected $message = 'Provided data mismatches!';
    protected $code = Response::HTTP_BAD_REQUEST;
}
