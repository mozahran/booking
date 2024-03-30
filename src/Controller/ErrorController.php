<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Exception\AppException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class ErrorController extends AbstractController
{
    public function show(
        \Throwable $exception,
    ): JsonResponse {
        $error = $exception->getMessage();
        if (!$exception instanceof AppException && 'prod' === strtolower($_ENV['APP_ENV'])) {
            $error = 'System error!';
        }

        return $this->json(
            data: ['error' => $error],
            status: $this->getStatusCode($exception),
        );
    }

    private function getStatusCode(
        \Throwable $exception,
    ): int {
        return !$exception->getCode() || $exception->getCode() >= 600 || 0 === $exception->getCode() ? 199 : $exception->getCode();
    }
}
