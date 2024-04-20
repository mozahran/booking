<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProviderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProviderRepository::class)]
#[ORM\Table(name: 'provider')]
class ProviderEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\OneToMany(targetEntity: ProviderUserDataEntity::class, mappedBy: 'provider', orphanRemoval: true)]
    private Collection $users;

    #[ORM\OneToMany(targetEntity: WorkspaceEntity::class, mappedBy: 'provider', orphanRemoval: true)]
    private Collection $workspaces;

    #[ORM\ManyToOne(targetEntity: UserEntity::class, inversedBy: 'providerEntities')]
    #[ORM\JoinColumn(nullable: false)]
    private UserEntity $user;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->workspaces = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    /**
     * @return Collection<int, ProviderUserDataEntity>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @return Collection<int, WorkspaceEntity>
     */
    public function getWorkspaces(): Collection
    {
        return $this->workspaces;
    }

    public function addWorkspace(WorkspaceEntity $workspace): static
    {
        if (!$this->workspaces->contains($workspace)) {
            $this->workspaces->add($workspace);
            $workspace->setProvider($this);
        }

        return $this;
    }

    public function removeWorkspace(WorkspaceEntity $workspace): static
    {
        if ($this->workspaces->removeElement($workspace)) {
            // set the owning side to null (unless already changed)
            if ($workspace->getProvider() === $this) {
                $workspace->setProvider(null);
            }
        }

        return $this;
    }

    public function getUser(): UserEntity
    {
        return $this->user;
    }

    public function setUser(UserEntity $user): static
    {
        $this->user = $user;

        return $this;
    }
}
