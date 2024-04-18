<?php

declare(strict_types=1);

namespace App\Domain\DataObject;

use App\Contract\DataObject\Identifiable;
use App\Contract\DataObject\Normalizable;

final readonly class User implements Normalizable, Identifiable
{
    /**
     * @param string[] $roles
     */
    public function __construct(
        private string $name,
        private string $email,
        #[\SensitiveParameter]
        private bool $active,
        private array $roles = [
            'ROLE_USER',
        ],
        private ?string $password = null,
        private ?int $id = null,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function normalize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'email' => $this->getEmail(),
            'active' => $this->isActive(),
            'roles' => $this->getRoles(),
        ];
    }
}
