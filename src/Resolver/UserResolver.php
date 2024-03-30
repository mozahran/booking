<?php

namespace App\Resolver;

use App\Contract\Repository\UserRepositoryInterface;
use App\Contract\Resolver\UserResolverInterface;
use App\Domain\DataObject\Set\UserSet;
use App\Domain\DataObject\User;

final readonly class UserResolver implements UserResolverInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function resolve(
        int $id,
    ): User {
        return $this->userRepository->findOne(id: $id);
    }

    public function resolveMany(): UserSet
    {
        return $this->userRepository->findMany();
    }
}
