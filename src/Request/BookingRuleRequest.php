<?php

namespace App\Request;

class BookingRuleRequest extends AbstractRequest
{
    public function getWorkspaceId(): int
    {
        $id = $this->request()->getPayload()->get('workspace')
            ?? $this->request()->get('workspace');

        return intval($id);
    }

    public function getName(string $default = ''): string
    {
        return $this->request()->get('name')
            ?? $this->request()->getPayload()->get('name', $default);
    }

    public function getContent(string $default = ''): string
    {
        return $this->request()->get('rule')
            ?? $this->request()->getPayload()->get('rule', $default);
    }

    public function getType(string $default = ''): string
    {
        return $this->request()->get('type')
            ?? $this->request()->getPayload()->get('type', $default);
    }

    public function isActive(bool $default = true): bool
    {
        return $this->request()->get('active')
            ?? $this->request()->getPayload()->get('active', $default);
    }

    public function getBookingRuleId(): int
    {
        return $this->request()->get('bookingRuleId');
    }
}
