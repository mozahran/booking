<?php

namespace App\Domain\DataObject;

use App\Contract\DataObject\Identifiable;
use App\Contract\DataObject\Normalizable;

final class Workspace implements Normalizable, Identifiable
{
    public function __construct(
        private string $name,
        private bool $active,
        private ?int $providerId = null,
        private ?int $id = null,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProviderId(): ?int
    {
        return $this->providerId;
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
            'provider_id' => $this->getProviderId(),
            'name' => $this->getName(),
            'active' => $this->isActive(),
        ];
    }
}
