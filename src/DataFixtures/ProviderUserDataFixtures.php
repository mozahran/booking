<?php

namespace App\DataFixtures;

use App\Entity\ProviderEntity;
use App\Entity\ProviderUserDataEntity;
use App\Entity\UserEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProviderUserDataFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $this->linkUser1WithProvider1AsAdmin($manager);
        $this->linkUser2WithProvider1AsUser($manager);
    }

    public function getDependencies(): array
    {
        return [
            ProviderFixtures::class,
        ];
    }

    private function linkUser1WithProvider1AsAdmin(ObjectManager $manager): void
    {
        /** @var UserEntity $user1 */
        $user1 = $this->getReference(UserFixtures::REF_01);

        /** @var ProviderEntity $provider1 */
        $provider1 = $this->getReference(ProviderFixtures::REF_01);

        $providerUserData = new ProviderUserDataEntity();
        $providerUserData->setUser($user1);
        $providerUserData->setProvider($provider1);
        $providerUserData->setRole('ROLE_OWNER');

        $manager->persist($providerUserData);
        $manager->flush();
    }

    private function linkUser2WithProvider1AsUser(ObjectManager $manager): void
    {
        /** @var UserEntity $user2 */
        $user2 = $this->getReference(UserFixtures::REF_02);

        /** @var ProviderEntity $provider1 */
        $provider1 = $this->getReference(ProviderFixtures::REF_01);

        $providerUserData = new ProviderUserDataEntity();
        $providerUserData->setUser($user2);
        $providerUserData->setProvider($provider1);
        $providerUserData->setRole('ROLE_USER');

        $manager->persist($providerUserData);
        $manager->flush();
    }
}
