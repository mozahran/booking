<?php

namespace App\Contract\Persistor;

use App\Domain\DataObject\User;
use App\Domain\Exception\AppException;
use App\Domain\Exception\UserNotFoundException;

interface UserPersistorInterface
{
    /**
     * @throws UserNotFoundException
     * @throws AppException
     */
    public function persist(
        User $user,
    ): User;
}
