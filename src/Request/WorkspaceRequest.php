<?php

namespace App\Request;

class WorkspaceRequest extends AbstractRequest
{
    public function getProviderId(): int
    {
        $id = $this->request()->get('providerId')
            ?? $this->request()->getPayload()->get('provider')
            ?? $this->request()->get('provider');

        return intval($id);
    }

    public function getWorkspaceId(): int
    {
        $id = $this->request()->get('workspaceId')
            ?? $this->request()->getPayload()->get('workspace')
            ?? $this->request()->get('workspace');

        return intval($id);
    }

    public function getName(): string
    {
        $value = $this->request()->getPayload()->get('name')
            ?? $this->request()->get('name');

        return strval($value);
    }
}
