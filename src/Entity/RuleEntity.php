<?php

namespace App\Entity;

use App\Repository\RuleEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'rule')]
#[ORM\Entity(repositoryClass: RuleEntityRepository::class)]
class RuleEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: SpaceEntity::class, inversedBy: 'rules')]
    #[ORM\JoinColumn(name: 'space_id', nullable: false)]
    private ?SpaceEntity $space = null;

    #[ORM\Column(name: 'type', type: 'string', length: 10, nullable: false)]
    private ?string $type = null;

    #[ORM\Column(name: 'content', type: Types::TEXT, nullable: false)]
    private ?string $content = null;

    #[ORM\Column(name: 'active', type: 'boolean', length: 1, nullable: false)]
    private ?bool $active = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSpace(): ?SpaceEntity
    {
        return $this->space;
    }

    public function setSpace(?SpaceEntity $space): static
    {
        $this->space = $space;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

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
