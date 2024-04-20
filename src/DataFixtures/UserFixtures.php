<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\UserEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    public const REF_01 = 'ref.user.1';
    public const REF_01_USERNAME = 'john.doe@example.com';
    public const REF_01_PASSWORD = '123456';
    public const REF_02 = 'ref.user.2';
    public const REF_02_USERNAME = 'jane.doe@example.com';
    public const REF_02_PASSWORD = '123456';

    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user1 = new UserEntity();
        $user1->setName('John Doe');
        $user1->setEmail(self::REF_01_USERNAME);
        $user1->setPassword($this->userPasswordHasher->hashPassword($user1, self::REF_01_PASSWORD));
        $user1->setRoles([
            'ROLE_ADMIN',
        ]);

        $manager->persist($user1);
        $manager->flush();

        $this->setReference(self::REF_01, $user1);

        $user2 = new UserEntity();
        $user2->setName('Jane Doe');
        $user2->setEmail(self::REF_02_USERNAME);
        $user2->setPassword($this->userPasswordHasher->hashPassword($user2, self::REF_02_PASSWORD));
        $user2->setRoles([
            'ROLE_ADMIN',
        ]);

        $manager->persist($user2);
        $manager->flush();

        $this->setReference(self::REF_02, $user2);
    }

    public static function getGroups(): array
    {
        return [
            'app',
            'test',
        ];
    }
}
