<?php

declare(strict_types=1);

namespace App\Domain\Enum;

enum UserRole: string
{
    case ADMIN = 'ROLE_ADMIN';
    case OWNER = 'ROLE_OWNER';
    case USER = 'ROLE_USER';

    /**
     * @param string[] $roles
     *
     * @return UserRole[]
     */
    public static function requireArray(array $roles): array
    {
        $result = [];
        foreach ($roles as $role) {
            $result[] = UserRole::from($role);
        }

        return $result;
    }
}
