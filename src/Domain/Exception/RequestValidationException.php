<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use Symfony\Component\HttpFoundation\Response;

class RequestValidationException extends AppException
{
    public function __construct(array $errors)
    {
        parent::__construct(
            message: implode(PHP_EOL, $errors),
            code: Response::HTTP_BAD_REQUEST,
        );
    }
}
