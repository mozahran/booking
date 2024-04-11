<?php

namespace App\Domain\DataObject;

use App\Contract\DataObject\Normalizable;
use App\Domain\Enum\RuleType;

final class Rule implements Normalizable
{
    public function __construct(
        private int $spaceId,
        private string $type,
        private string $content,
        private bool $active = true,
        private ?int $id = null,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSpaceId(): int
    {
        return $this->spaceId;
    }

    public function getType(): RuleType
    {
        return RuleType::from($this->type);
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function normalize(): array
    {
        return [
            'id' => $this->getId(),
            'spaceId' => $this->getSpaceId(),
            'type' => $this->getType(),
            'content' => $this->getContent(),
            'active' => $this->isActive(),
        ];
    }
}
