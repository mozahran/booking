<?php

namespace App\Domain\DataObject;

use App\Contract\DataObject\Normalizable;
use App\Domain\Enum\RuleType;

final readonly class BookingRule implements Normalizable
{
    public function __construct(
        private int $workspaceId,
        private string $name,
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

    public function getWorkspaceId(): int
    {
        return $this->workspaceId;
    }

    public function getName(): string
    {
        return $this->name;
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
        $rule = json_decode(json: $this->getContent(), associative: true);

        return [
            'id' => $this->getId(),
            'workspaceId' => $this->getWorkspaceId(),
            'type' => $this->getType(),
            'content' => $rule,
            'active' => $this->isActive(),
        ];
    }
}
