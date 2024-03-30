<?php

declare(strict_types=1);

namespace App\Contract\Translator;

use App\Domain\DataObject\Set\UserSet;
use App\Domain\DataObject\User;
use App\Domain\Exception\UserNotFoundException;
use App\Entity\UserEntity;

interface UserTranslatorInterface
{
    public function toUser(
        UserEntity $entity,
    ): User;

    /**
     * @param UserEntity[] $entities
     */
    public function toUserSet(
        array $entities,
    ): UserSet;

    /**
     * @throws UserNotFoundException
     */
    public function toUserEntity(
        User $user,
    ): UserEntity;
}
