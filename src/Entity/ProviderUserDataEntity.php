<?php

namespace App\Entity;

use App\Repository\ProviderUserDataRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProviderUserDataRepository::class)]
#[ORM\UniqueConstraint(columns: ['provider_id', 'user_id'])]
#[ORM\Table(name: 'provider_user_data')]
class ProviderUserDataEntity
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: ProviderEntity::class, inversedBy: 'providerUserData')]
    #[ORM\JoinColumn(nullable: false)]
    private ProviderEntity $provider;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: UserEntity::class, inversedBy: 'providerUserData')]
    #[ORM\JoinColumn(nullable: false)]
    private UserEntity $user;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $active = true;

    #[ORM\Column(type: 'string', length: 255, nullable: false, options: ['default' => 'ROLE_USER'])]
    private string $role = 'ROLE_USER';

    public function getProvider(): ProviderEntity
    {
        return $this->provider;
    }

    public function setProvider(ProviderEntity $provider): static
    {
        $this->provider = $provider;

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

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }
}
