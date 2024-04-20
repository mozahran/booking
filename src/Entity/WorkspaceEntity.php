<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\WorkspaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkspaceRepository::class)]
#[ORM\Table(name: 'workspace')]
class WorkspaceEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(targetEntity: SpaceEntity::class, mappedBy: 'workspace', orphanRemoval: true)]
    private Collection $spaces;

    #[ORM\Column]
    private ?bool $active = true;

    #[ORM\ManyToOne(inversedBy: 'workspaces')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProviderEntity $provider = null;

    #[ORM\OneToMany(targetEntity: BookingRuleEntity::class, mappedBy: 'space', orphanRemoval: true)]
    private Collection $rules;

    public function __construct()
    {
        $this->spaces = new ArrayCollection();
        $this->rules = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, SpaceEntity>
     */
    public function getSpaces(): Collection
    {
        return $this->spaces;
    }

    public function addSpace(SpaceEntity $space): static
    {
        if (!$this->spaces->contains($space)) {
            $this->spaces->add($space);
            $space->setWorkspace($this);
        }

        return $this;
    }

    public function removeSpace(SpaceEntity $space): static
    {
        if ($this->spaces->removeElement($space)) {
            // set the owning side to null (unless already changed)
            if ($space->getWorkspace() === $this) {
                $space->setWorkspace(null);
            }
        }

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getProvider(): ?ProviderEntity
    {
        return $this->provider;
    }

    public function setProvider(?ProviderEntity $provider): static
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * @return Collection<int, BookingRuleEntity>
     */
    public function getRules(): Collection
    {
        return $this->rules;
    }
}
