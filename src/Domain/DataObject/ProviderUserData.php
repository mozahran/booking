<?php

declare(strict_types=1);

namespace App\Domain\DataObject;

use App\Domain\Enum\UserRole;

final class ProviderUserData
{
    public function __construct(
        private int $providerId,
        private int $userId,
        private string $role,
        private bool $active,
    ) {
    }

    public function getProviderId(): int
    {
        return $this->providerId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getRole(): UserRole
    {
        return UserRole::from($this->role);
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}
