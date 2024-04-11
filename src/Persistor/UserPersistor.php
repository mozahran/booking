<?php

namespace App\Persistor;

use App\Contract\Persistor\UserPersistorInterface;
use App\Contract\Translator\UserTranslatorInterface;
use App\Domain\DataObject\User;
use App\Domain\Exception\AppException;
use App\Domain\Exception\UserNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

final class UserPersistor implements UserPersistorInterface
{
    public function __construct(
        private UserTranslatorInterface $userTranslator,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function persist(
        User $user,
    ): User {
        $entity = $this->userTranslator->toUserEntity($user);
        if (!$entity->getId()) {
            $this->entityManager->persist($entity);
        }
        try {
            $this->entityManager->flush();
        } catch (\Throwable $exception) {
            if (str_contains($exception->getMessage(), 'UNIQ_IDENTIFIER_EMAIL')) {
                throw new AppException('This email has been used before!');
            }
        }
        try {
            $this->entityManager->refresh($entity);
        } catch (ORMException) {
            throw new UserNotFoundException($entity->getId());
        }

        return $this->userTranslator->toUser($entity);
    }
}
