<?php

declare(strict_types=1);

namespace App\Contract\Repository;

use App\Domain\DataObject\Set\UserSet;
use App\Domain\DataObject\User;
use App\Domain\Exception\UserNotFoundException;

interface UserRepositoryInterface
{
    /**
     * @throws UserNotFoundException
     */
    public function findOne(
        int $id,
    ): User;

    /**
     * @throws UserNotFoundException
     */
    public function findOneByEmail(
        string $email,
    ): User;

    public function findMany(): UserSet;

    public function activate(
        int $id,
    ): void;

    public function deactivate(
        int $id,
    ): void;
}
