<?php

namespace App\Domain\DataObject;

use App\Contract\DataObject\Identifiable;
use App\Contract\DataObject\Normalizable;

final readonly class Provider implements Normalizable, Identifiable
{
    public function __construct(
        private string $name,
        private bool $active,
        private ?int $userId = null,
        private ?int $id = null,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
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
            'name' => $this->getName(),
            'active' => $this->isActive(),
        ];
    }
}
