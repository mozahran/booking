<?php

namespace App\DataFixtures;

use App\Domain\Enum\UserRole;
use App\Entity\ProviderEntity;
use App\Entity\ProviderUserDataEntity;
use App\Entity\UserEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProviderUserDataFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->linkUser1WithProvider1AsAdmin($manager);
        $this->linkUser1WithProvider2AsUser($manager);
        $this->linkUser2WithProvider2AsAdmin($manager);
    }

    public function getDependencies(): array
    {
        return [
            ProviderFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return [
            'app',
            'test',
        ];
    }

    private function linkUser1WithProvider1AsAdmin(ObjectManager $manager): void
    {
        $this->createProviderUserDataEntity(
            manager: $manager,
            user: $this->getTestUser01(),
            provider: $this->getTestProvider01(),
            userRole: UserRole::ADMIN,
        );
    }

    private function linkUser1WithProvider2AsUser(ObjectManager $manager): void
    {
        $this->createProviderUserDataEntity(
            manager: $manager,
            user: $this->getTestUser01(),
            provider: $this->getTestProvider02(),
            userRole: UserRole::USER,
        );
    }

    private function linkUser2WithProvider2AsAdmin(ObjectManager $manager): void
    {
        $this->createProviderUserDataEntity(
            manager: $manager,
            user: $this->getTestUser02(),
            provider: $this->getTestProvider02(),
            userRole: UserRole::ADMIN,
        );
    }

    private function getTestUser01(): UserEntity
    {
        return $this->getReference(
            name: UserFixtures::REF_01,
            class: UserEntity::class,
        );
    }

    private function getTestUser02(): UserEntity
    {
        return $this->getReference(
            name: UserFixtures::REF_02,
            class: UserEntity::class,
        );
    }

    private function getTestProvider01(): object
    {
        return $this->getReference(
            name: ProviderFixtures::REF_01,
            class: ProviderEntity::class,
        );
    }

    private function getTestProvider02(): object
    {
        return $this->getReference(
            name: ProviderFixtures::REF_02,
            class: ProviderEntity::class,
        );
    }

    private function createProviderUserDataEntity(
        ObjectManager $manager,
        UserEntity $user,
        ProviderEntity $provider,
        UserRole $userRole,
    ): void {
        $providerUserData = new ProviderUserDataEntity();
        $providerUserData->setUser($user);
        $providerUserData->setProvider($provider);
        $providerUserData->setRole($userRole->value);

        $manager->persist($providerUserData);
        $manager->flush();
    }
}
