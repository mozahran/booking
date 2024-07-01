<?php

declare(strict_types=1);

namespace App\Contract\Resolver;

use App\Domain\DataObject\Set\UserSet;
use App\Domain\DataObject\User;
use App\Domain\Exception\UserNotFoundException;

interface UserResolverInterface
{
    /**
     * @throws UserNotFoundException
     */
    public function resolve(
        int $id,
    ): User;

    /**
     * @throws UserNotFoundException
     */
    public function resolveByEmail(
        string $email
    ): User;

    public function resolveMany(): UserSet;
}
