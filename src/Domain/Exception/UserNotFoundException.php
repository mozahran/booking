<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use Symfony\Component\HttpFoundation\Response;

class UserNotFoundException extends AppException
{
    protected $code = Response::HTTP_BAD_REQUEST;

    public function __construct(int $id)
    {
        $message = sprintf('Requested user not found. (ID: %d)', $id);

        parent::__construct($message, $this->code);
    }
}
