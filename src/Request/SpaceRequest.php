<?php

namespace App\Request;

class SpaceRequest extends AbstractRequest
{
    public function getWorkspaceId(): int
    {
        $id = $this->request()->getPayload()->get('workspace')
            ?? $this->request()->get('workspace');

        return intval($id);
    }

    public function getSpaceId(): int
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
