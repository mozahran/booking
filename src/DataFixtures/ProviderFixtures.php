<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\ProviderEntity;
use App\Entity\UserEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProviderFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const REF_01 = 'ref.provider.1';
    public const REF_02 = 'ref.provider.2';

    public function load(ObjectManager $manager): void
    {
        /** @var UserEntity $user1 */
        $user1 = $this->getReference(UserFixtures::REF_01, UserEntity::class);
        /** @var UserEntity $user2 */
        $user2 = $this->getReference(UserFixtures::REF_02, UserEntity::class);

        $provider1 = new ProviderEntity();
        $provider1->setName('Provider #1');
        $provider1->setUser($user1);

        $provider2 = new ProviderEntity();
        $provider2->setName('Provider #2');
        $provider2->setUser($user2);

        $manager->persist($provider1);
        $manager->persist($provider2);

        $manager->flush();

        $this->setReference(self::REF_01, $provider1);
        $this->setReference(self::REF_02, $provider2);
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return [
            'app',
            'test',
        ];
    }
}
