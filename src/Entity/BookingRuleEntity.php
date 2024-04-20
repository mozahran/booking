<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Enum\RuleType;
use App\Repository\BookingRuleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'booking_rule')]
#[ORM\Entity(repositoryClass: BookingRuleRepository::class)]
class BookingRuleEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: WorkspaceEntity::class, inversedBy: 'rules')]
    #[ORM\JoinColumn(name: 'workspace_id', nullable: false)]
    private ?WorkspaceEntity $workspace = null;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: false)]
    private ?string $name = null;

    #[ORM\Column(name: 'type', type: 'string', length: 20, nullable: false)]
    private ?string $type = null;

    #[ORM\Column(name: 'content', type: Types::TEXT, nullable: false)]
    private ?string $content = null;

    #[ORM\Column(name: 'active', type: 'boolean', length: 1, nullable: false)]
    private ?bool $active = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWorkspace(): ?WorkspaceEntity
    {
        return $this->workspace;
    }

    public function setWorkspace(?WorkspaceEntity $workspace): static
    {
        $this->workspace = $workspace;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(RuleType $type): static
    {
        $this->type = $type->value;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }
}
