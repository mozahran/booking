<?php

namespace App\Request;

class ProviderRequest extends AbstractRequest
{
    public function getName(): string
    {
        $value = $this->request()->getPayload()->get('name')
            ?? $this->request()->get('name');

        return strval($value);
    }

    public function getUserId(): int
    {
        $id = $this->request()->getPayload()->get('user')
            ?? $this->request()->get('user');

        return intval($id);
    }
}
