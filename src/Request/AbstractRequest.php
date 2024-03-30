<?php

namespace App\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class AbstractRequest
{
    private Request $request;

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    protected function request(): ?Request
    {
        if (!isset($this->request)) {
            $this->request = $this->requestStack->getCurrentRequest();
        }

        return $this->request;
    }
}
