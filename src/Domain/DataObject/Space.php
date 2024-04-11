<?php

namespace App\Domain\DataObject;

use App\Contract\DataObject\Identifiable;
use App\Contract\DataObject\Normalizable;

final class Space implements Normalizable, Identifiable
{
    public function __construct(
        private string $name,
        private bool $active,
        private ?int $workspaceId = null,
        private ?int $id = null,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWorkspaceId(): ?int
    {
        return $this->workspaceId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function normalize(): array
    {
        return [
            'id' => $this->getId(),
            'workspace_id' => $this->getWorkspaceId(),
            'name' => $this->getName(),
            'active' => $this->isActive(),
        ];
    }
}
