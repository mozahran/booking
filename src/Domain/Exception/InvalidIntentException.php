<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use App\Domain\Enum\CancellationIntent;
use Symfony\Component\HttpFoundation\Response;

class InvalidIntentException extends AppException
{
    protected $code = Response::HTTP_BAD_REQUEST;

    public function __construct(
        string $intentType,
    ) {
        $message = sprintf(
            'Intent type "%s" is invalid. Supported types: %s, %s, %s',
            $intentType,
            CancellationIntent::ALL->value,
            CancellationIntent::SELECTED->value,
            CancellationIntent::SELECTED_AND_FOLLOWING->value,
        );

        parent::__construct($message, $this->code);
    }
}
