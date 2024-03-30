<?php

namespace App\Request;

class UserRequest extends AbstractRequest
{
    public function getUserId(): int
    {
        $id = $this->request()->getPayload()->get('user')
            ?? $this->request()->get('user');

        return intval($id);
    }

    public function getName(): string
    {
        $value = $this->request()->getPayload()->get('name')
            ?? $this->request()->get('name');

        return strval($value);
    }

    public function getEmail(): string
    {
        $value = $this->request()->getPayload()->get('email')
            ?? $this->request()->get('email');

        return strval($value);
    }

    public function getPassword(): string
    {
        $value = $this->request()->getPayload()->get('password');

        return strval($value);
    }
}
